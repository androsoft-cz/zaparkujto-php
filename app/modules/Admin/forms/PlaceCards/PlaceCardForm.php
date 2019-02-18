<?php

namespace App\Modules\Admin\Forms\PlaceCards;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Nette\Forms\IControl;

final class PlaceCardForm extends BaseForm
{

    /** @var UsersRepository */
    private $usersRepository;

    /** @var PlacesRepository */
    private $placesRepository;

    /** @var int */
    private $organizationId;

    /** @var int */
    private $placeId;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        UsersRepository $usersRepository,
        PlacesRepository $useofPlacesRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->usersRepository = $usersRepository;
        $this->placesRepository = $useofPlacesRepository;

        $this->addText('email', 'common.email')
            ->setRequired('forms.placecards.rule.email')
            ->addRule(self::EMAIL, 'forms.placecards.rule.emailFormat')
            ->addRule([$this, 'emailValidation'], 'forms.placecards.rule.unknownEmail');

        $this->addSubmit('submit', 'forms.placecards.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }


    /**
     * @param int $placeId
     */
    public function setPlaceId($placeId)
    {
        $this->placeId = $placeId;
    }


    public function processForm()
    {
        $values = $this->getValues();

        /** @var User $user */
        $user = $this->usersRepository->findBy([
            'username' => $values->email,
            'organization' => $this->organizationId,
        ])->fetch();

        /** @var Place $place */
        $place = $this->placesRepository->getById($this->placeId);

        $user->place = $place;

        $this->usersRepository->persistAndFlush($user);
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

        return (bool) $user;
    }

}
