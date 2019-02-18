<?php

namespace App\Modules\Admin\Forms\Users;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;

final class UserForm extends BaseForm
{

    /** @var int */
    private $organizationId;

    /** @var UsersRepository */
    public $usersRepository;

    /** @var OrganizationsRepository */
    public $organizationRepository;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        UsersRepository $usersRepository,
        OrganizationsRepository $organizationsRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->usersRepository = $usersRepository;
        $this->organizationRepository = $organizationsRepository;

        $this->addGroup('');
        $this->addText('username', 'forms.user.login')
            ->setOption('description', '(email)');

        $this->addText('name', 'forms.user.name');

        $this->addText('role', 'forms.user.role')
            ->setOption('description', '(admin, controlor, user)');

        $this->addText('password_new', 'forms.user.password');

        $this->addHidden('orgId');
        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.user.submit');

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
            $user->createdAt = new \DateTime;
        }

        $user->username = $values->username;
        $user->name = $values->name;
        $user->role = $values->role;
        if ($values->password_new) {
            $user->setPassword($values->password_new);
        }

        $user->contact->email = $values->username;
        $user->contact->state = 'Česká republika';

        $this->usersRepository->persistAndFlush($user);
    }


    /**
     * @param int
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

}
