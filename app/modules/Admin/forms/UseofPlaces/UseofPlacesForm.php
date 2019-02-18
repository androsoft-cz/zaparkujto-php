<?php

namespace App\Modules\Admin\Forms\UseofPlaces;

use App\Core\Forms\BaseForm;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\UseofPlaces\UseofPlacesRepository;
use DateInterval;
use Kdyby\Translation\Translator;

final class UseofPlacesForm extends BaseForm
{
    /** @var UseofPlacesRepository */
    private $useofPlacesRepository;

    /** @var Place */
    private $place;

    /** @var UseofPlaces|NULL */
    private $useofPlaces;


    public function __construct(
        Translator $translator,
        WeekDayTranslator $weekDayTranslator,
        UseofPlacesRepository $useofPlacesRepository,
        Place $place,
        UseofPlaces $useofPlaces = NULL
    ) {
        parent::__construct();

        $this->setTranslator($translator);

        $this->useofPlacesRepository = $useofPlacesRepository;
        $this->place = $place;
        $this->useofPlaces = $useofPlaces;

        $this->addSelect('type', 'forms.place.type', [
            UseofPlaces::TYPE_PROSPECT => 'forms.place.types.prospect',
            UseofPlaces::TYPE_RESIDENT => 'forms.place.types.resident',
            UseofPlaces::TYPE_HANDICAPPED => 'forms.place.types.hendicapped',
            UseofPlaces::TYPE_AUTOMAT => 'forms.place.types.automat',
            UseofPlaces::TYPE_TIMEDISC => 'forms.place.types.timedisc',
            UseofPlaces::TYPE_SELECTED => 'forms.place.types.selected',
            UseofPlaces::TYPE_FREEZONE => 'forms.place.types.free',
            UseofPlaces::TYPE_SHAREABLE => 'forms.place.types.shared',
            UseofPlaces::TYPE_SMSZONE => 'forms.place.types.smszone',
        ]);

        $this->addCheckbox('reservedForZaparkujto', 'forms.place.reservedForZaparkujto');

        $this->addText('pricePerUnit', 'forms.place.price');

        $this->addText('pricePerExtend', 'forms.place.priceExtend');

        $this->addText('validFrom', 'admin.validFrom')
            ->setDefaultValue('00:00')
            ->setType('time')
            ->setRequired('admin.msgFillTimeFrom');

        $this->addText('validTo', 'admin.validTo')
            ->setDefaultValue('23:59')
            ->setType('time')
            ->setRequired('admin.msgFillTimeTo');

        $this->addCheckbox('validHoursInverted', 'admin.invertValidTime');

        $this->addText('_validDaysPlaceHolder'); // holding place for validDays label

        $validDays = $this->addContainer('validDays');

        for ($i = 1; $i <= 7; $i++) {
            $validDays->addCheckbox($i, $weekDayTranslator->translate($i))
                ->setDefaultValue(TRUE);
        }

        $this->addCheckbox('advanceReservation', 'forms.place.advanceReservation');

        $this->addText('maxReservationTime', 'forms.place.maxReservationTime')
            ->setDefaultValue('02:00')
            ->setType('time')
            ->setRequired('forms.place.rule.maxReservationTime');

        $this->addSubmit('submit', 'forms.place.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm(): void
    {
        $values = $this->getValues();

        if ($this->useofPlaces === NULL) {
            $useofPlaces = new UseofPlaces();
            $useofPlaces->place = $this->place;
        } else {
            $useofPlaces = $this->useofPlaces;
        }

        $useofPlaces->type = $values->type;
        $useofPlaces->pricePerUnit = $values->pricePerUnit;
        $useofPlaces->pricePerExtend = $values->pricePerExtend;
        $useofPlaces->validHoursInverted = $values->validHoursInverted ? 1 : 0;
        $useofPlaces->reservedForZaparkujto = $values->reservedForZaparkujto ? 1 : 0;

        for ($i = 1; $i <= 7; $i++) {
            $useofPlaces->setValidDay($i, $values->validDays->$i);
        }

        $useofPlaces->advanceReservation = $values->advanceReservation ? 1 : 0;

        [$hours, $minutes] = sscanf($values->maxReservationTime, '%d:%d');
        $useofPlaces->maxReservationTime = $hours * 60 + $minutes;

        [$hours, $minutes] = sscanf($values->validFrom, '%d:%d');
        $useofPlaces->validFrom = new DateInterval("PT{$hours}H{$minutes}M0S");

        [$hours, $minutes] = sscanf($values->validTo, '%d:%d');
        $useofPlaces->validTo = new DateInterval("PT{$hours}H{$minutes}M0S");

        $this->useofPlacesRepository->persistAndFlush($useofPlaces);
    }
}
