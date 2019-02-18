<?php

namespace App\Modules\Driver\Forms\Profiles;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UserRzHelper;
use Kdyby\Translation\Translator;
use Nette\Forms\IControl;
use Nette\Utils\Html;

final class ProfileForm extends BaseForm
{
    /** @var int */
    private $organizationId;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var OrganizationsRepository */
    private $organizationRepository;

    /** @var UserRzHelper */
    private $userRzHelper;


    public function __construct(
        Translator $translator,
        UsersRepository $usersRepository,
        OrganizationsRepository $organizationsRepository,
        UserRzHelper $userRzHelper
    ) {
        parent::__construct();

        $this->setTranslator($translator);

        $this->usersRepository = $usersRepository;
        $this->organizationRepository = $organizationsRepository;
        $this->userRzHelper = $userRzHelper;

        $this->addText('name', 'forms.card.name')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.nameLength', 255);

        $this->addText('email', 'forms.card.email')
            ->setRequired('forms.card.rule.email')
            ->addRule(self::EMAIL, 'forms.card.rule.emailFormat')
            ->addRule([$this, 'emailValidation'], 'forms.card.rule.emailUsed');

        $this->addText('telephone', 'forms.card.phone')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.phoneLength', 255);

        $this->addText('address1', 'forms.card.street')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.addressLength', 255);

        $this->addText('address2', 'forms.card.city')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.addressLength', 255);

        $this->addText('company', 'forms.card.company')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.companyLength', 255);

        $this->addText('identificationNumber', 'forms.card.cin')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.cinLength', 255);

        $this->addText('taxIdentificationNumber', 'forms.card.tin')
            ->addCondition(self::FILLED)
            ->addRule(self::MAX_LENGTH, 'forms.card.rule.tinLength', 255);

        $this->addText('rz', 'forms.card.rz')
            ->setRequired('forms.card.rule.rz')
            ->setOption('description', 'forms.card.rzHelpText');

        $this->addText('creditAmount', 'forms.profile.creditAmount')
            ->setDisabled();

        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.card.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm()
    {
        $values = $this->getValues();

        /** @var User $user */
        $user = $this->usersRepository->getById($values->id);

        $user->name = $values->name;
        $user->contact->address1 = $values->address1;
        $user->contact->address2 = $values->address2;
        $user->contact->company = $values->company;
        $user->contact->identificationNumber = $values->identificationNumber;
        $user->contact->taxIdentificationNumber = $values->taxIdentificationNumber;
        $user->contact->email = $values->email;
        $user->contact->telephone = $values->telephone;

        $this->usersRepository->persistAndFlush($user);

        $registrationNumbers = explode(',', $values->rz);
        $this->userRzHelper->updateRzs($user, $registrationNumbers);
    }


    /**
     * @param int
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
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
