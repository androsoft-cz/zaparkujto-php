<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Mailing\MailService;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use Nette\Utils\DateTime;

final class NotificationFacade
{

    /** @var Config */
    private $config;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var MailService */
    private $mailService;

    /**
     * @param Config $config
     * @param ReservationsRepository $reservationsRepository
     * @param MailService $mailService
     */
    public function __construct(
        Config $config,
        ReservationsRepository $reservationsRepository,
        MailService $mailService
    )
    {
        $this->config = $config;
        $this->reservationsRepository = $reservationsRepository;
        $this->mailService = $mailService;
    }

    /**
     * @return Reservation[]
     */
    public function getSoonExpireReservations()
    {
        /** @var Reservation[] $reservations */
        $reservations = $this->reservationsRepository->findBy([
            'this->order->state' => Order::STATE_PAID,
            'state' => [
                Reservation::STATE_NORMAL,
                Reservation::STATE_EXTENDED,
            ],
            'from<' => new DateTime(),
            'to>' => new DateTime(),
            'notifiedAt' => NULL,
        ]);

        $_reservations = [];
        $notification = $this->config->expand('diff.notification');
        $tolerance = $this->config->expand('logic.tolerance_time');
        foreach ($reservations as $reservation) {

            // If next reservation is on same user, same RZ
            // and at double tolerance limit, then skip it
            if (($next = $reservation->place->getNextReservation(new DateTime()))) {
                if ($next->user === $reservation->user
                    && $next->rz === $reservation->rz
                    && $next->from < DateTime::from($reservation->to)->modifyClone('+ ' . (2 * $tolerance) . ' minutes')
                ) {
                    // Skip it
                    continue;
                }
            }

            // If reservation ends at less then limit
            if ($reservation->to < new DateTime("+ $notification minutes")) {
                $_reservations[] = $reservation;
            }
        }

        return $_reservations;
    }

    /**
     * @param Reservation $reservation
     */
    public function sendSoonExpireNotification(Reservation $reservation)
    {
        // Can extend?
        $canExtend = TRUE;
        if (($nextReservation = $reservation->place->getNextReservation(DateTime::from($reservation->to)))) {
            // If next reservations starts too soon, extending is not allowed
            $minutes = $this->config->expand('logic.tolerance_time') * 2 + $this->config->expand('diff.minimal');
            if ($nextReservation->from < DateTime::from($reservation->to)->modifyClone("+ $minutes minutes")) {
                $canExtend = FALSE;
            }
        }

        // Send email
        $this->mailService->sendCustomerReservationSoonExpire($reservation, $canExtend);

        // Update reservation
        $reservation->notifiedAt = new DateTime();
        $this->reservationsRepository->persistAndFlush($reservation);
    }

}
