<?php

namespace App\Modules\Driver;

use App\Model\Facade\OrderFacade;
use App\Model\Facade\PlaceFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UsersRzMapper;
use App\Model\Search\FreeCapacityCounter;
use App\Model\Search\SearchArgs;
use App\Model\Search\UseofPlaceSelector;
use App\Model\WebServices\Locator\PlaceTypeIconLocator;
use App\Modules\Driver\Forms\Order\IReservationFormFactory;
use App\Modules\Driver\Forms\Order\ReservationForm;
use Nette\Application\BadRequestException;
use Nextras\Orm\Collection\ICollection;

final class ReservationPresenter extends SecurePresenter
{
    /** @var IReservationFormFactory @inject */
    public $reservationFormFactory;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var PlaceFacade @inject */
    public $placeFacade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var ReservationsRepository @inject */
    public $reservationsRepository;

    /** @var FreeCapacityCounter @inject */
    public $freeCapacityCounter;

    /** @var UseofPlaceSelector @inject */
    public $useofPlaceSelector;

    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var PlaceTypeIconLocator @inject */
    public $placeTypeIconLocator;

    /** @var Place */
    private $place;

    /** @var UseofPlaces */
    private $useofPlace;

    /** @var UsersRzMapper @inject */
    public $usersRzMapper;


    public function renderDefault()
    {
        /** @var Reservation[] $waitingReservations */
        $waitingReservations = $this->reservationsRepository->findWaiting($this->user->id);
        $waitingReservations = $this->sortAndLimitReservations($waitingReservations);

        /** @var Reservation[] $presentReservations */
        $presentReservations = $this->reservationsRepository->findPresent($this->user->id);
        $presentReservations = $this->sortAndLimitReservations($presentReservations);

        /** @var Reservation[] $lastReservations */
        $lastReservations = $this->reservationsRepository->findLast($this->user->id);
        $lastReservations = $this->sortAndLimitReservations($lastReservations);

        /** @var Reservation[] $futureReservations */
        $futureReservations = $this->reservationsRepository->findFuture($this->user->id);
        $futureReservations = $this->sortAndLimitReservations($futureReservations);

        $this->template->waitingReservations = $this->getReservationItems($waitingReservations);
        $this->template->presentReservations = $this->getReservationItems($presentReservations);
        $this->template->lastReservations = $this->getReservationItems($lastReservations);
        $this->template->futureReservations = $this->getReservationItems($futureReservations);
    }


    public function renderDetail($id)
    {
        /** @var Reservation $reservation */
        $reservation = $this->reservationsRepository->getById($id);

        if (!$reservation) {
            throw new BadRequestException("Reservation '$id' not found.");
        }

        if ($reservation->useofPlaces->isForFree()) {
            $price = $this->translator->translate('common.forFree');
        } elseif ($reservation->useofPlaces->type == UseofPlaces::TYPE_RESIDENT) {
            $price = $this->translator->translate('driver.creditsCount', ['credits' => $reservation->price]);
        } else {
            $price = $reservation->price . ' ' . $this->placeFacade->getStringCurrency($reservation->place);
        }

        $result = [
            'reservation_id' => $reservation->id,
            'organization_id' => $reservation->place->organization->id,
            'place_id' => $reservation->place->id,
            'street_name' => $reservation->place->streetName,
            'identifier' => $reservation->place->identifier,
            'lng' => $reservation->place->lng,
            'lat' => $reservation->place->lat,
            'rz' => $reservation->rz,
            'price' => $price,
            'from_date' => $reservation->from->format('j. n. Y H:i'),
            'to_date' => $reservation->to->format('j. n. Y H:i'),
            'typeIconUrl' => $this->placeTypeIconLocator->getUrl($reservation->useofPlaces->type),
        ];

        if (isset($reservation->order)) {
            $result += [
                'paid_at' => ($reservation->order->paidAt) ? $reservation->order->paidAt->format('c') : NULL,
                'order_state' => $reservation->order->state,
                'order_state_key' => $this->orderFacade->getStateTranslationKey($reservation->order->state),
                'waitingForPayment' => $reservation->order->state == Order::STATE_WAITING,
            ];

            if (isset($reservation->order->paymentType)) {
                $result += ['payment_type' => $reservation->order->paymentType];
            }
        }

        $this->template->reservation = (object) $result;
    }


    /**
     * @param int $id
     */
    public function actionPlace($id)
    {
        $this->place = $this->placesRepository->getById($id);

        if (!$this->place) {
            $this->error('Place not found');
        }

        $searchArgs = new SearchArgs();
        $searchArgs->reserveFrom = time();
        $this->useofPlace = $this->useofPlaceSelector->getUseofPlace($this->place, $searchArgs);

        if (!$this->useofPlace) {
            $this->error('UseofPlace not found');
        }

        if ($this->useofPlace->type == UseofPlaces::TYPE_PROSPECT) {
            $this->error('Unsupported place type');
        }

        /** @var User $user */
        $user = $this->usersRepository->getById($this->user->id);
        $rsz = $this->usersRzMapper->findAllUserRzs($user);

        $defaults = [
            'rz' => reset($rsz),
        ];

        /** @var ReservationForm $form */
        $form = $this->getComponent('reservationForm');
        $form->setDefaults($defaults);
    }


    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderPlace($id)
    {
        $place = $this->place;

        $searchArgs = new SearchArgs();
        $searchArgs->reserveFrom = time();

        $this->template->place = (object) [
            'identifier' => $place->identifier,
            'streetName' => $place->streetName,
            'isForFree' => $this->useofPlace->isForFree(),
            'free_capacity' => $this->freeCapacityCounter->count($place, $this->useofPlace, $searchArgs),
            'typeIconUrl' => $this->placeTypeIconLocator->getUrl($this->useofPlace->type),
        ];

        $this->template->showReservationForm = $this->useofPlace->type != UseofPlaces::TYPE_FREEZONE;
    }


    public function actionCancel($id)
    {
        /** @var Reservation $reservation */
        $reservation = $this->reservationsRepository->getById($id);

        if (!$reservation) {
            throw new BadRequestException("Reservation '$id' not found.");
        }

        if ($reservation->order->state == Order::STATE_STORNO) {
            $this->flashMessage('driver.msgReservationAlreadyCanceled', 'alert');
            $this->redirect(':Driver:Reservation:detail', $id);
        } else {
            $this->orderFacade->cancelOrderByReservation($id);
            $this->flashMessage('driver.msgCancelReservationSucceed', 'success');
            $this->redirect(':Driver:Reservation:detail', $id);
        }
    }


    public function actionGateway($id)
    {
        /** @var Reservation $reservation */
        $reservation = $this->reservationsRepository->getById($id);

        if (!$reservation) {
            throw new BadRequestException("Reservation '$id' not found.");
        }

        if ($reservation->order->state == Order::STATE_WAITING && $reservation->order->gatewayUrl !== NULL) {
            $this->redirectUrl($reservation->order->gatewayUrl);
        } else {
            $this->redirect(':Driver:Reservation:detail', $id);
        }
    }


    /**
     * @return ReservationForm
     */
    protected function createComponentReservationForm()
    {
        /** @var User $user */
        $user = $this->usersRepository->getById($this->user->id);

        $form = $this->reservationFormFactory->create($this->place, $this->useofPlace, $user, $this);

        $form->onSuccess[] = function () use ($form) {
            if ($form->placeReservatorResult->gatewayResult['gw_url'] !== NULL) {
                $this->redirectUrl($form->placeReservatorResult->gatewayResult['gw_url']);
            } else {
                $this->flashMessage('driver.msgReservationSucceed', 'success');
                $this->redirect(':Driver:Reservation:detail', $form->placeReservatorResult->reservation->id);
            }
        };

        return $form;
    }


    /**
     * @param Reservation[]|ICollection $reservations
     * @return Reservation[]|ICollection
     */
    private function sortAndLimitReservations($reservations)
    {
        return $reservations->orderBy('createdAt', ICollection::DESC)
            ->orderBy('from', ICollection::DESC)
            ->limitBy(50);
    }


    /**
     * @param Reservation[] $reservations
     * @return object[]
     */
    private function getReservationItems($reservations)
    {
        $reservationItems = [];

        /** @var Reservation $reservation */
        foreach ($reservations as $reservation) {
            $reservationItems[] = (object) [
                'reservation_id' => $reservation->id,
                'place_id' => $reservation->place->id,
                'organization_id' => $reservation->place->organization->id,
                'street_name' => $reservation->place->streetName,
                'identifier' => $reservation->place->identifier,
                'lng' => $reservation->place->lng,
                'lat' => $reservation->place->lat,
                'rz' => $reservation->rz,
                'price' => $reservation->price,
                'from_date' => $reservation->from->format('j. n. Y H:i'),
                'to_date' => $reservation->to->format('j. n. Y H:i'),
                'payment_type' => $reservation->order->paymentType,
                'paid_at' => ($reservation->order->paidAt) ? $reservation->order->paidAt->format('c') : NULL,
                'order_state' => $reservation->order->state,
            ];
        }

        return $reservationItems;
    }

}
