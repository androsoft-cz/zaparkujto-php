<?php

namespace App\Model\Reservations;

use App\Model\Exceptions\Runtime\OrderException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Exceptions\Runtime\ReserveException;
use App\Model\Exceptions\Runtime\ReserveValidationException;
use App\Model\Facade\OrderFacade;
use App\Model\Facade\ReserveFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Orders\OrdersRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Payment\PaymentService;
use App\Model\Search\SearchArgs;
use App\Model\Search\UseofPlaceSelector;
use Kdyby\Translation\Translator;
use Markette\GopayInline\Api\Lists\PaymentInstrument;
use Nette\Utils\DateTime;

class PlaceReservator
{
    /** @var PlacesRepository */
    private $placesRepository;

    /** @var OrdersRepository */
    private $ordersRepository;

    /** @var ReserveFacade */
    private $reserveFacade;

    /** @var OrderFacade */
    private $orderFacade;

    /** @var Translator */
    private $translator;

    /** @var ReservationValidator */
    private $reservationValidator;

    /** @var UseofPlaceSelector */
    private $useofPlaceSelector;

    /** @var PaymentService */
    private $paymentService;

    public function __construct(
        PlacesRepository $placesRepository,
        OrdersRepository $ordersRepository,
        ReserveFacade $reserveFacade,
        OrderFacade $orderFacade,
        Translator $translator,
        ReservationValidator $reservationValidator,
        UseofPlaceSelector $useofPlaceSelector,
        PaymentService $paymentService
    ) {
        $this->placesRepository = $placesRepository;
        $this->ordersRepository = $ordersRepository;
        $this->reserveFacade = $reserveFacade;
        $this->orderFacade = $orderFacade;
        $this->translator = $translator;
        $this->reservationValidator = $reservationValidator;
        $this->useofPlaceSelector = $useofPlaceSelector;
        $this->paymentService = $paymentService;
    }


    public function check(PlaceReservatorContext $context): ?\stdClass
    {
        if ($context->reserveTo !== NULL && $context->parkingTime !== NULL) {
            throw new ReserveException('Wrong ReservatorContext. Both parameters reserveTo and parkingTime is set.');
        }

        if ($context->reserveTo === NULL && $context->parkingTime === NULL) {
            throw new ReserveException('Wrong ReservatorContext. Parameters reserveTo or parkingTime is not set.');
        }

        /** @var Place $place */
        $place = $this->placesRepository->getById($context->placeId);

        if (!$place) {
            throw new ReserveException("Place '$context->placeId' not found.");
        }

        if ($context->reserveFrom === NULL) {
            $context->reserveFrom = time();
        }

        if (($context->reserveTo === NULL) && ($context->parkingTime !== NULL)) {
            $context->reserveTo = $context->reserveFrom + $context->parkingTime * 60;
        }

        if ($context->parkingTime === NULL) {
            $context->parkingTime = ($context->reserveTo - $context->reserveFrom) / 60;
        }

        $placeUsage = $this->reservationValidator->validateReservation(
            $place,
            $context->reserveFrom,
            $context->parkingTime,
            $context->user
        );

        // TODO: dopracovat kontrolu na obsah v `reservations`. V tuto chvili neresime, protoze umoznujeme rezervaci i pri prekroceni poctu volnych mist

        //fake result structure:
        $result = new \stdClass;
        $from = new DateTime('@' . $context->reserveFrom);
        $to = new DateTime('@' . $context->reserveTo);
        $result->price = $this->reserveFacade->calculateReservationPrice($from, $to, $placeUsage);
        $result->from = $from->format('c');
        $result->to = $to->format('c');
        return $result;

    }


    /**
     * @param PlaceReservatorContext $context
     * @param int
     * @return PlaceReservatorResult
     */
    public function reserve(PlaceReservatorContext $context, $paymentType = Order::PAYMENT_TYPE_GATE)
    {
        $result = new PlaceReservatorResult();

        if ($context->reserveTo !== NULL && $context->parkingTime !== NULL) {
            throw new ReserveException('Wrong ReservetorContext. Both parameters reserveTo and parkingTime is set.');
        }

        if ($context->reserveTo === NULL && $context->parkingTime === NULL) {
            throw new ReserveException('Wrong ReservetorContext. Parameters reserveTo or parkingTime is not set.');
        }

        /** @var Place $place */
        $place = $this->placesRepository->getById($context->placeId);

        if (!$place) {
            throw new ReserveException("Place '$context->placeId' not found.");
        }

        if ($context->reserveFrom === NULL) {
            $context->reserveFrom = time();
        }

        if (($context->reserveTo === NULL) && ($context->parkingTime !== NULL)) {
            $context->reserveTo = $context->reserveFrom + $context->parkingTime * 60;
        }

        if ($context->parkingTime === NULL) {
            $context->parkingTime = ($context->reserveTo - $context->reserveFrom) / 60;
        }

        $datetimeFrom = DateTime::from($context->reserveFrom);
        $datetimeTo = DateTime::from($context->reserveTo);

        // Validate reservation (is it free? etc.)
        $this->reservationValidator->validateReservation(
            $place,
            $context->reserveFrom,
            $context->parkingTime,
            $context->user
        );

        try {
            $searchArgs = new SearchArgs();
            $searchArgs->reserveFrom = $context->reserveFrom;
            $searchArgs->parkTime = $context->parkingTime;
            $useofPlace = $this->useofPlaceSelector->getUseofPlace($place, $searchArgs);

            /** @var Reservation $reservation */
            $reservation = $this->reserveFacade->createReservation($place, $useofPlace, $datetimeFrom, $datetimeTo, $context->rz);
            $result->reservation = $reservation;

            if (!$context->isCheck) {
                if ($useofPlace->type == UseofPlaces::TYPE_AUTOMAT || $useofPlace->type == UseofPlaces::TYPE_SHAREABLE || $paymentType == Order::PAYMENT_TYPE_SMS) {
                    if ($paymentType == Order::PAYMENT_TYPE_SMS) {
                        // Create order
                        $order = $this->orderFacade->createOrder($context->user, [$reservation], Order::PAYMENT_TYPE_SMS);
                        $result->order = $order;
                    } else {
                        // Create order
                        $order = $this->orderFacade->createOrder($context->user, [$reservation], Order::PAYMENT_TYPE_GATE, $context->usePaymentCard);

                        try {
                            // Pay order
                            $gatewayResult = $this->paymentService->createPayment($place->organization, $order, PaymentInstrument::PAYMENT_CARD);

                            // Store payment id
                            $order->paymentId = (string) $gatewayResult['paymentId'];
                            $order->state = Order::STATE_WAITING;
                            $order->gatewayUrl = $gatewayResult['gw_url'];
                            $order->payeeId = $gatewayResult['payeeId'];
                            $this->ordersRepository->persistAndFlush($order);

                            $result->order = $order;
                            $result->gatewayResult = $gatewayResult;
                        } catch (PaymentException $e) {
                            throw new OrderException('Failed during persists payment ID', $e->getCode(), $e->getPrevious());
                        }
                    }
                } elseif ($useofPlace->type == UseofPlaces::TYPE_RESIDENT) {
                    // Create order
                    $order = $this->orderFacade->createOrder($context->user, [$reservation], Order::PAYMENT_TYPE_CREDIT);

                    // Pay order
                    $this->orderFacade->payOrderByCredit($order, $context->user);

                    $result->order = $order;
                } elseif ($useofPlace->isForFree()) {
                    // Create order
                    $order = $this->orderFacade->createOrder($context->user, [$reservation], Order::PAYMENT_TYPE_FREE);

                    // Pay order
                    $this->orderFacade->payOrderByFree($order);

                    $result->order = $order;
                } else {
                    throw new ReserveException("Unsupported UseOfPlace type '$useofPlace->type'");
                }
            }
        } catch (OrderException $e) {
            if ($e->hasApiMessage()) {
                throw $e;
            } else {
                throw (new ReserveException($this->translator->translate('rpmApi.orderProcessingError')))
                    ->setApiMessage('orderProcessingError');
            }
        }

        return $result;
    }

}
