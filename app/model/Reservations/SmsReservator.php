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

class SmsReservator
{
    /** @var PlacesRepository */
    private $placesRepository;

    /** @var ReserveFacade */
    private $reserveFacade;

    /** @var OrderFacade */
    private $orderFacade;

    /** @var Translator */
    private $translator;

    /** @var UseofPlaceSelector */
    private $useofPlaceSelector;

    public function __construct(
        PlacesRepository $placesRepository,
        ReserveFacade $reserveFacade,
        OrderFacade $orderFacade,
        Translator $translator,
        UseofPlaceSelector $useofPlaceSelector
    ) {
        $this->placesRepository = $placesRepository;
        $this->reserveFacade = $reserveFacade;
        $this->orderFacade = $orderFacade;
        $this->translator = $translator;
        $this->useofPlaceSelector = $useofPlaceSelector;
    }



    /**
     * @param PlaceReservatorContext $context
     * @param int
     * @return PlaceReservatorResult
     */
    public function reserve(Place $place, DateTime $reserveFrom, $parkingTime, $rz, $price, $paymentType = Order::PAYMENT_TYPE_SMS)
    {
        $result = new PlaceReservatorResult();

        if (!$place) {
            throw new ReserveException('Place not found.');
        }

        if ($reserveFrom === NULL) {
            throw new ReserveException('ReserveFrom is null');
        }

        if ($parkingTime === NULL) {
            throw new ReserveException('ParkingTime is null');
        }

        $reserveTo = $reserveFrom->modifyClone('+'.$parkingTime.' minute');

        try {
            $searchArgs = new SearchArgs();
            $searchArgs->reserveFrom = $reserveFrom->getTimestamp();
            $searchArgs->parkTime = $parkingTime;
            $useofPlace = $this->useofPlaceSelector->getUseofPlace($place, $searchArgs);

            /** @var Reservation $reservation */
            $reservation = $this->reserveFacade->createReservation($place, $useofPlace, $reserveFrom, $reserveTo, $rz, $price);
            $result->reservation = $reservation;

            $order = $this->orderFacade->createOrder(NULL, [$reservation], $paymentType);
            $result->order = $order;
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
