<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Exceptions\Runtime\InvalidReservationStateException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Mailing\MailService;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Orders\VerifiedOrder;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\ReservationTokens\ReservationTokensRepository;
use App\Model\Payment\PaymentService;
use Nette\Utils\DateTime;
use Tracy\Debugger;

final class ExtendReservationFacade extends BaseReservationFacade
{

    /** @var MailService */
    private $mailService;

    /** @var PaymentService */
    private $paymentService;


    /**
     * ExtendReservationFacade constructor.
     *
     * @param Config                      $config
     * @param ReservationTokensRepository $reservationTokensRepository
     * @param ReservationsRepository      $reservationsRepository
     * @param MailService                 $mailService
     * @param PaymentService              $paymentService
     */
    public function __construct(
        Config $config,
        ReservationTokensRepository $reservationTokensRepository,
        ReservationsRepository $reservationsRepository,
        MailService $mailService,
        PaymentService $paymentService
    )
    {
        parent::__construct($config, $reservationTokensRepository, $reservationsRepository);
        $this->mailService = $mailService;
        $this->paymentService = $paymentService;
    }

    /**
     * @param int $id Reservation ID
     * @return Reservation
     */
    public function getReservation($id)
    {
        return $this->reservationsRepository->getById($id);
    }

    /**
     * @param Reservation $reservation
     * @param int $extends
     * @param bool $persist
     * @throws InvalidStateException
     * @throws InvalidReservationStateException
     * @return Reservation
     */
    public function createExtendReservation(Reservation $reservation, $extends, $persist = TRUE)
    {
        if ($reservation->to < new DateTime()) {
            throw new InvalidStateException('Reservation already end');
        }

        if ($reservation->state !== Reservation::STATE_NORMAL) {
            throw new InvalidReservationStateException($reservation->state);
        }

        // Update reservation
        $newTo = DateTime::from($reservation->to)->modifyClone("+ $extends minutes");

        $newReservation = new Reservation();
        /** @var Order $order */
        $order = $reservation->order->toClone(); //vytvori novou objednavku na zaklade te stare. protoze to preci ma byt nova objednavka
        $newReservation->order = $order;
        $newReservation->user = $reservation->user;
        $newReservation->place = $reservation->place;
        $newReservation->state = Reservation::STATE_WAITING_EXTEND;
        $newReservation->from = $reservation->to;
        $newReservation->to = $newTo;
        $newReservation->rz = $reservation->rz;
        $newReservation->extended = $reservation;
        $ratio = $extends / 60;
        $newReservation->price = ceil($reservation->useofPlaces->pricePerExtend * $ratio);

        // Persist
        $reservation->extendToken->appliedAt = new DateTime();
        if ($persist == TRUE) {
            $this->reservationsRepository->persistAndFlush($reservation);
        }

        return $newReservation;
    }

    /**
     * @param Reservation $extended
     * @throws InvalidReservationStateException
     */
    public function affectConcurrenceReservations(Reservation $extended)
    {
        $from = DateTime::from($extended->from);
        $newTo = DateTime::from($extended->to);

        $affectedReservations = $this->reservationsRepository
            // Find all concurrence reservations
            ->findConcurrences($extended->place, $from, $newTo)
            // Exclude my-self reservation
            ->findBy(['id!=' => ($extended->extended === NULL ? $extended->id : [$extended->id, $extended->extended->id])]);

        foreach ($affectedReservations as $affectedReservation) {

            // If affected reservation is already extended,
            // throws exception
            if ($affectedReservation->state === Reservation::STATE_EXTENDED) {
                throw new InvalidReservationStateException($affectedReservation->state);
            }

            // If place has spare place and spare place is free
            $fullrefund = TRUE;
            if ($affectedReservation->place->sparePlace) {
                $sparePlaceReservations = $this->reservationsRepository->findConcurrences(
                    $affectedReservation->place->sparePlace, DateTime::from($affectedReservation->from), DateTime::from($affectedReservation->to)
                );
                $fullrefund = $sparePlaceReservations->countStored() > 0;
            }

            if ($fullrefund) {
                // No spare place of spare place is not free
                try {
                    // Refund payment
                    $this->paymentService->refundPayment($affectedReservation, $affectedReservation->price);
                } catch (PaymentException $e) { // Skip
                }

                // Update reservation
                $affectedReservation->state = Reservation::STATE_CANCELLED_AND_REFUND;
                $affectedReservation->originAt = new DateTime();
                $affectedReservation->originPrice = $affectedReservation->price;
                $affectedReservation->price = 0;
                $affectedReservation->order->state = Order::STATE_FULL_REFUNDED;
                $this->reservationsRepository->persistAndFlush($affectedReservation);

                // Inform customer
                $this->mailService->sendCustomerReservationCancelledAndRefund($affectedReservation);
            } else {
                // Create offered reservations according to affected
                $offeredReservation = new Reservation();
                $offeredReservation->user = $affectedReservation->user;
                $offeredReservation->order = $affectedReservation->order;
                $offeredReservation->place = $affectedReservation->place->sparePlace;
                $offeredReservation->state = Reservation::STATE_RESERVED;
                $offeredReservation->from = $affectedReservation->from;
                $offeredReservation->to = $affectedReservation->to;
                $offeredReservation->price = $affectedReservation->price;
                $offeredReservation->rz = $affectedReservation->rz;

                // Create accept token
                $token = $this->reservationTokensRepository->createUniqueToken();
                $token->type = $token::TYPE_EXTEND_SPARE;
                $offeredReservation->tokens->add($token);
                // Persist
                $this->reservationsRepository->persistAndFlush($offeredReservation);

                // Cancel previous
                $affectedReservation->state = Reservation::STATE_CANCELLED;
                $affectedReservation->originAt = new DateTime();
                $affectedReservation->originPrice = $affectedReservation->price;
                $affectedReservation->price = 0;
                $this->reservationsRepository->persistAndFlush($affectedReservation);

                // Inform customer
                $this->mailService->sendCustomerReservationCancelledAndOfferSparePlace($affectedReservation, $offeredReservation);
            }
        }
    }

    /**
     * @param Reservation $reservation
     */
    public function acceptSpareReservation(Reservation $reservation)
    {
        // Update reservation
        $reservation->state = Reservation::STATE_NORMAL;
        $reservation->extendSpareToken->appliedAt = new DateTime();
        $this->reservationsRepository->persistAndFlush($reservation);

        // Inform customer
        $this->mailService->sendCustomerAcceptSpareReservation($reservation);
    }

    /**
     * @param Reservation $reservation
     */
    public function rejectSpareReservation(Reservation $reservation)
    {
        try {
            // Refund payment
            $this->paymentService->refundPayment($reservation, $reservation->price);
        } catch (PaymentException $e) {
            Debugger::log($e);
            throw new PaymentException('Failed during processing refund', 0, $e);
        }

        // Update reservation
        $reservation->state = Reservation::STATE_CANCELLED_AND_REFUND;
        $reservation->originAt = new DateTime();
        $reservation->originPrice = $reservation->price;
        $reservation->price = 0;
        $reservation->order->state = Order::STATE_FULL_REFUNDED;
        $reservation->extendSpareToken->appliedAt = new DateTime();
        $this->reservationsRepository->persistAndFlush($reservation);
    }

    /**
     * @param Reservation $reservation
     * @param VerifiedOrder $verified
     */
    public function applyPayment(Reservation $reservation, VerifiedOrder $verified)
    {
        if ($reservation->state !== Reservation::STATE_WAITING_EXTEND) {
            throw new InvalidStateException('Invalid reservation state');
        }

        if ($verified->isPaid()) {
            // Update reservation
            $reservation->state = Reservation::STATE_EXTENDED;
            $this->reservationsRepository->persistAndFlush($reservation);

            // Affect concurrent reservations
            $this->affectConcurrenceReservations($reservation);
        }
    }

}
