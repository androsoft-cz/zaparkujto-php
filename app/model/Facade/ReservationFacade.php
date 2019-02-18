<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Payment\PaymentService;
use Nette\Utils\DateTime;
use Nextras\Orm\Collection\ICollection;
use Tracy\Debugger;

final class ReservationFacade
{

    /** @var Config */
    private $config;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var PaymentService */
    private $paymentService;

    /**
     * @param Config $config
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(
        Config $config,
        ReservationsRepository $reservationsRepository,
        PaymentService $paymentService
    )
    {
        $this->config = $config;
        $this->reservationsRepository = $reservationsRepository;
        $this->paymentService = $paymentService;
    }

    /**
     * @return Reservation[]|ICollection
     */
    public function getCancelableReservedReservations()
    {
        $blocking = $this->config->expand('reservation.extend.extend_blocking_time');

        return $this->reservationsRepository->findBy([
            'state' => Reservation::STATE_RESERVED,
            'createdAt<' => new DateTime("- $blocking minutes"),
        ]);
    }


    public function getUnpaidExpiredReservations(): ICollection
    {
        return $this->reservationsRepository->findBy([
            'state' => Reservation::STATE_NORMAL,
            'to<' => new DateTime(),
            'this->order->state' => Order::STATE_WAITING,
        ]);
    }


    public function cancelReserved(Reservation $reservation): void
    {
        try {
            if ($reservation->order->state === Order::STATE_PAID) {
                // Vrácení platby zpět plátci
                $this->paymentService->refundPayment($reservation, $reservation->price);
                $reservation->order->state = Order::STATE_FULL_REFUNDED;
            }
        } catch (PaymentException $e) {
            Debugger::log($e);
            throw new PaymentException('Failed during processing refund', 0, $e);
        }

        $reservation->state = Reservation::STATE_CANCELLED;
        $this->reservationsRepository->persistAndFlush($reservation);
    }
}
