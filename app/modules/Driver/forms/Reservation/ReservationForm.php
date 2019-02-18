<?php

namespace App\Modules\Driver\Forms\Order;

use App\Core\Forms\BaseForm;
use App\Model\Exceptions\Traits\IApiMessageException;
use App\Model\Facade\PlaceFacade;
use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use App\Model\Reservations\PlaceReservatorResult;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Presenter;

final class ReservationForm extends BaseForm
{
    /** @var PlaceReservatorResult */
    public $placeReservatorResult;

    /** @var Place */
    private $place;

    /** @var UseofPlaces */
    private $useofPlace;

    /** @var User */
    private $user;

    /** @var Presenter */
    private $presenter;

    /** @var PlaceFacade */
    private $placeFacade;

    /** @var PlaceReservator */
    private $placeReservator;


    public function __construct(
        Place $place,
        UseofPlaces $useofPlace,
        User $user,
        Presenter $presenter,
        Translator $translator,
        PlaceFacade $placeFacade,
        PlaceReservator $placeReservator
    ) {
        parent::__construct();

        $this->setTranslator($translator);

        $this->place = $place;
        $this->useofPlace = $useofPlace;
        $this->user = $user;
        $this->presenter = $presenter;
        $this->placeFacade = $placeFacade;
        $this->placeReservator = $placeReservator;

        $this->addSelect('rz', 'driver.rz', $this->createRzItems())
            ->setRequired(TRUE);

        $now = time();

        $this->addText('from', 'driver.parkingFrom')
            ->setDisabled()
            ->setDefaultValue($this->formatTime($now));

        $parkTimeItems = $this->createParkTimeItems();

        $this->addSelect('parkTime', 'driver.parkingTime', $parkTimeItems)
            ->setTranslator(NULL);

        $this->addSelect('to', 'driver.parkingTo', $this->createToItems($parkTimeItems, $now))
            ->setDisabled()
            ->setTranslator(NULL);

        $this->addSelect('price', 'common.price', $this->createPriceItems($parkTimeItems))
            ->setDisabled();

        $this->addSubmit('submit', $this->useofPlace->isForFree() ? 'driver.reserve' : 'driver.pay');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm(): void
    {
        $values = $this->getValues();

        $context = new PlaceReservatorContext();
        $context->placeId = $this->place->id;
        $context->parkingTime = $values->parkTime;
        $context->user = $this->user;
        $context->rz = $values->rz;
        $context->presenter = $this->presenter;

        try {
            $this->placeReservatorResult = $this->placeReservator->reserve($context);
        } catch (IApiMessageException $e) {
            if ($e->hasApiMessage()) {
                $this->addError($e->getMessage());
            } else {
                throw $e;
            }
        }
    }


    private function createParkTimeItems(): array
    {
        $translator = $this->getTranslator();
        $items = [
            30 => $translator->translate('driver.minutesCount', ['minutes' => 30]),
        ];

        foreach ([60, 90, 120, 150, 180, 210, 240, 270, 300] as $minutes) {
            if ($this->useofPlace->maxReservationTime < $minutes) {
                break;
            }

            $items[$minutes] = $translator->translate('driver.minutesCount', ['minutes' => $minutes]);
        }

        if ($this->useofPlace->maxReservationTime > 300) {
            $items[$this->useofPlace->maxReservationTime] = $translator->translate('driver.minutesCount', ['minutes' => $this->useofPlace->maxReservationTime]);
        }

        return $items;
    }


    private function createToItems(array $parkTimeItems, int $timestamp): array
    {
        $items = [];

        foreach ($parkTimeItems as $minutes => $parkTimeItem) {
            $items[$minutes] = $this->formatTime($timestamp + $minutes * 60);
        }

        return $items;
    }


    private function createPriceItems(array $parkTimeItems): array
    {
        if ($this->useofPlace->isForFree()) {
            return [0 => 'common.forFree'];
        }

        $items = [];

        foreach ($parkTimeItems as $minutes => $parkTimeItem) {
            $items[$minutes] = number_format($this->useofPlace->pricePerUnit * $minutes / 60, 2) . ' ' . $this->placeFacade->getStringCurrency($this->place);
        }

        return $items;
    }


    private function formatTime(int $timestamp): string
    {
        return date('d.m.Y H:i', $timestamp);
    }


    private function createRzItems(): array
    {
        return $this->user->userRzs->get()->fetchPairs('rz', 'rz');
    }
}
