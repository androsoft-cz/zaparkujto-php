<?php

namespace App\Modules\Admin\Forms\Cards;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UserRz;
use App\Model\Orm\UsersRz\UsersRzRepository;
use App\Model\Security\Authorizator;
use Nette\Forms\IControl;

final class CardForm extends BaseForm
{

    /** @var int */
    private $organizationId;

    /** @var Place|NULL */
    private $place;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var OrganizationsRepository */
    private $organizationRepository;

    /** @var PlacesRepository */
    private $placesRepository;

    /** @var UsersRzRepository */
    private $usersRzRepository;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        UsersRepository $usersRepository,
        OrganizationsRepository $organizationsRepository,
        PlacesRepository $placesRepository,
        UsersRzRepository $usersRzRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->usersRepository = $usersRepository;
        $this->organizationRepository = $organizationsRepository;
        $this->placesRepository = $placesRepository;
        $this->usersRzRepository = $usersRzRepository;

        $this->addGroup('Detail vlastníka');
        $this->addText('name', 'forms.card.name')
            ->setRequired('forms.card.rule.name');

        $this->addText('email', 'forms.card.email')
            ->setRequired('forms.card.rule.email')
            ->addRule(self::EMAIL, 'forms.card.rule.emailFormat')
            ->addRule([$this, 'emailValidation'], 'forms.card.rule.emailUsed');

        $this->addText('telephone', 'forms.card.phone')
            ->setRequired('forms.card.rule.phone');

        $this->addText('company', 'forms.card.company');

        $this->addText('identification_number', 'forms.card.cin');

        $this->addText('tax_identification_number', 'forms.card.tin');

        $this->addCheckbox('handicapped', 'forms.card.handicapped');

        $this->addGroup('Adresa');

        $this->addText('address1', 'forms.card.street')
            ->setRequired('forms.card.rule.street');

        $this->addText('address2', 'forms.card.city')
            ->setRequired('forms.card.rule.city');

        $this->addText('place', 'forms.card.place');

        $this->addGroup('Parkovací karta');

        $this->addText('rz', 'forms.card.rz');
        $this->addText('cenik', 'Ceník');
        $this->addText('typkarty', 'Typ karty');
        $this->addText('parkoviste', 'Parkoviště');

        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.card.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm()
    {
        $values = $this->getValues();

        if ($values->id != '') { // update
            /** @var User $user */
            $user = $this->usersRepository->getById($values->id);
        } else { // insert
            $organization = $this->organizationRepository->getById($this->organizationId);

            /** @var User $user */
            $user = new User;
            $user->organization = $organization;
            $user->contact = new Contact;
            $user->setPassword(\Nette\Utils\Random::generate(20));
            $user->role = Authorizator::USER;
            $user->createdAt = new \DateTime;
        }

        $user->username = $values->email;
        $user->name = $values->name;
        $user->place = $this->place;
        $user->handicapped = (int) $values->handicapped;
        $user->contact->email = $values->email;
        $user->contact->telephone = $values->telephone;
        $user->contact->company = $values->company;
        $user->contact->identificationNumber = $values->identification_number;
        $user->contact->taxIdentificationNumber = $values->tax_identification_number;
        $user->contact->address1 = $values->address1;
        $user->contact->address2 = $values->address2;
        $user->contact->state = 'Česká republika';

        $this->usersRepository->persistAndFlush($user);

        $usersRz = new UserRz;
        $usersRz->user = $user;
        $usersRz->rz = $values->rz;

        $this->usersRzRepository->persistAndFlush($usersRz);
    }


    /**
     * @param int
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }


    /**
     * @param int|NULL $defaultPlaceId
     */
    public function prepareForAdd($defaultPlaceId)
    {
        $placeId = $defaultPlaceId;

        if ($this->isSubmitted()) {
            $placeId = $this->getValues()->place;
        }

        if ($placeId) {
            $this->place = $this->placesRepository->getById($placeId);

            if ($this->place) {
                $this->setDefaults(['place' => $placeId]);
            }
        }
    }


    /**
     * @param User $user
     */
    public function prepareForEdit(User $user)
    {
        if ($this->isSubmitted()) {
            $placeId = $this->getValues()->place;
            $this->place = $this->placesRepository->getById($placeId);
        } else {
            $this->place = $user->place;
        }
    }


    /**
     * @return string|NULL
     */
    public function getPlaceName()
    {
        return $this->place->identifier;
    }


    /**
     * @return bool
     */
    public function placeInputVisible()
    {
        return (bool) $this->place;
    }


    /**
     * @param IControl $control
     * @return bool
     */
    public function emailValidation(IControl $control)
    {
        /** @var User $user */
        $user = $this->usersRepository->findBy([
            'username' => $control->getValue(),
            'organization' => $this->organizationId,
        ])->fetch();

        if ($user) {
            return $user->id == $this->getValues()->id;
        }

        return TRUE;
    }

}
