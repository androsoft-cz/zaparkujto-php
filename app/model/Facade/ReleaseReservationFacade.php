<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Exceptions\Logical\RequestedResourceIsNotAvaiableException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Model;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\ReservationTokens\ReservationTokensRepository;
use App\Model\Payment\PaymentService;
use Markette\GopayInline\Exception\GopayException;
use Nette\Utils\DateTime;
use Tracy\Debugger;

final class ReleaseReservationFacade extends BaseReservationFacade
{

    /** @var Model */
    private $model;

    /** @var PaymentService */
    private $paymentService;


    /**
     * ReleaseReservationFacade constructor.
     *
     * @param Config                      $config
     * @param ReservationTokensRepository $reservationTokenRepository
     * @param ReservationsRepository      $reservationRepository
     * @param Model                       $model
     * @param PaymentService              $paymentService
     */
    public function __construct(
        Config $config,
        ReservationTokensRepository $reservationTokenRepository,
        ReservationsRepository $reservationRepository,
        Model $model,
        PaymentService $paymentService
    )
    {
        parent::__construct($config, $reservationTokenRepository, $reservationRepository);
        $this->model = $model;
        $this->paymentService = $paymentService;
    }


    /**
     * @param Reservation $reservation
     * @throws InvalidStateException
     * @return Reservation
     */
    public function releaseReservation(Reservation $reservation)
    {
        if ($reservation->to < new DateTime()) {
            throw new RequestedResourceIsNotAvaiableException('Reservation already end');
        }

        if ($reservation->state !== Reservation::STATE_NORMAL) {
            throw new InvalidStateException('Cannot release reservation in this state');
        }

        $minutes = $this->config->expand('reservation.release.canceled_before');
        if ($reservation->from > new DateTime("+ $minutes minutes")) {
            // Refund 100%
            $refund = 100;
        } else {
            // Refund x%
            $refund = $this->config->expand('reservation.release.canceled_during');
        }

        // Prepare arguments
        if ($refund == 100) {
            $amount = $reservation->price;
        } else {
            $timeNow = time();
            $timeFrom = $reservation->from->format('U');
            $timeTo = $reservation->to->format('U');

            $reservationDiff = $timeTo - $timeFrom;
            $currentDiff = $timeTo - $timeNow;

            $amount = ($currentDiff / $reservationDiff) * $reservation->price;
            $amount = round($amount * ($refund / 100));
        }

        // Pokud byla rezervace zaplacena platebni branou
        if ($reservation->order->paymentType == Order::PAYMENT_TYPE_GATE) {
            $paymentId = $reservation->order->paymentId;

            if (!$paymentId) {
                throw new InvalidStateException('No payment ID');
            }

            try {
                // Refund payment
                $this->paymentService->refundPayment($reservation, $amount);
            } catch (PaymentException $e) {
                Debugger::log($e);
                throw new PaymentException('Failed during processing refund', 0, $e);
            }
        }

        // Pokud byla rezervace placena kreditem
        if ($reservation->order->paymentType == Order::PAYMENT_TYPE_CREDIT) {
            /** @var Credit $credit */
            $credit = new Credit();
            $credit->order = $reservation->order;
            $credit->price = $amount;
            $credit->createdAt = new DateTime();
            $credit->movementType = Credit::MOVEMENT_TYPE_REFUND;
            $credit->user = $reservation->user;
            $this->model->credits->persist($credit);
        }

        // Update reservation
        $reservation->state = $refund == 100 ? Reservation::STATE_FULL_RELEASED : Reservation::STATE_RELEASED;
        $reservation->originAt = new DateTime();
        $reservation->originPrice = $reservation->price;
        $reservation->price = $reservation->price - $amount;
        // If is it partial released, set new time to
        $reservation->to = $refund == 100 ? $reservation->to : new DateTime();
        // => order
        $reservation->order->state = $refund == 100 ? Order::STATE_FULL_REFUNDED : Order::STATE_PARTIALY_REFUNDED;
        // => token
        $reservation->releaseToken->appliedAt = new DateTime();

        // Persist reservation
        $this->model->reservations->persist($reservation);
        $this->model->flush();

        return $reservation;
    }

}
