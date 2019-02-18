<?php

namespace App\Modules\Admin\Forms\Places;

use App\Core\Forms\BaseForm;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use Kdyby\Translation\Translator;
use Nette\Utils\Random;
use Nette\Utils\Strings;

final class PlaceForm extends BaseForm
{
    /** @var WeekDayTranslator */
    private $weekDayTranslator;

    /** @var PlacesRepository */
    private $placesRepository;

    /** @var OrganizationsRepository */
    private $organizationsRepository;


    public function __construct(
        Translator $translator,
        WeekDayTranslator $weekDayTranslator,
        OrganizationsRepository $organizationsRepository,
        PlacesRepository $placesRepository
    ) {
        parent::__construct();

        $this->setTranslator($translator);

        $this->weekDayTranslator = $weekDayTranslator;
        $this->placesRepository = $placesRepository;
        $this->organizationsRepository = $organizationsRepository;

        $this->addGroup('');
        $this->addText('identifier', 'forms.place.identifier');

        $this->addText('streetName', 'forms.place.street')
            ->setRequired('forms.place.rule.street');

        $this->addSelect('currency', 'common.currency', [
            Place::CURRENCY_CZK => 'CZK',
            Place::CURRENCY_EUR => 'EUR',
            Place::CURRENCY_PLN => 'PLN',
        ]);

        $this->addText('capacity', 'forms.place.capacity')
            ->addRule($this::FILLED, 'forms.place.rule.capacity')
            ->addRule($this::INTEGER, 'forms.place.rule.capacityInt');

        $this->addText('lat', 'forms.place.lat');
        $this->addText('lng', 'forms.place.lng');
        $this->addText('locationRadius', 'forms.place.radius');
        $this->addText('smsKeyword', 'forms.place.smsKeyword');

        $this->addTextArea('description', 'forms.place.description');

        $this->addCheckbox('countFreePlaces', 'forms.place.countFreePlaces');

        $this->addCheckbox('spareSignature', 'forms.place.spare');

        $this->addSelect('sparePlace', 'forms.place.spareSelect')
            ->setPrompt('forms.place.empty');

        $this->addHidden('id');
        $this->addHidden('org_id', '1');
        $this->addSubmit('submit', 'forms.place.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    /**
     * @param int
     */
    public function setSparePlaces($organization): void
    {
        $spareSplaces = $this->placesRepository->findSparePlaces($organization);
        $this['sparePlace']->setItems($spareSplaces);
    }


    public function processForm(): void
    {
        $values = $this->getValues();

        if ($values->id) {
            /** @var Place $place */
            $place = $this->placesRepository->getById($values->id);
        } else {
            $organization = $this->organizationsRepository->getById($this->getPresenter()->myUserData['org_id']);

            /** @var Place $place */
            $place = new Place();
            $place->organization = $organization;
            $place->smsKeyword = Strings::upper(Random::generate(6));
        }

        $place->identifier = $values->identifier;
        $place->streetName = $values->streetName;
        $place->currency = $values->currency;
        $place->description = $values->description;
        $place->lat = $values->lat;
        $place->lng = $values->lng;
        $place->locationRadius = $values->locationRadius;
        $place->name = NULL;
        $place->spareSignature = $values->spareSignature ? 1 : 0;
        $place->sparePlace = $values->sparePlace;
        $place->capacity = $values->capacity;
        $place->smsKeyword = $values->smsKeyword;
        $place->countFreePlaces = $values->countFreePlaces;

        $this->placesRepository->persistAndFlush($place);
    }
}
