<?php

namespace App\Modules\Driver\Forms\PasswordReset;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Kdyby\Translation\Translator;
use Nette\Forms\Form;

final class PasswordResetForm extends BaseForm
{
    /** @var UsersRepository */
    private $usersRepository;

    /** @var User */
    private $user;


    public function __construct(
        Translator $translator,
        UsersRepository $usersRepository,
        User $user
    ) {
        parent::__construct();
        $this->setTranslator($translator);

        $this->usersRepository = $usersRepository;
        $this->user = $user;

        $newPasswordInput = $this->addPassword('newPassword', 'driver.newPassword')
            ->setRequired()
            ->addRule(Form::MIN_LENGTH, $this->getTranslator()->translate('driver.passwordLengthAtLeast', ['length' => 6]), 6);

        $this->addPassword('confirmNewPassword', 'driver.confirmNewPassword')
            ->setRequired()
            ->addRule(Form::EQUAL, 'driver.passwordDoNotMatch', $newPasswordInput);

        $this->addSubmit('submit', 'driver.changePassword');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm()
    {
        $values = $this->getValues();

        $this->user->setPassword($values->newPassword);
        $this->user->passwordResetToken = NULL;
        $this->user->passwordResetTokenGeneratedAt = NULL;
        $this->usersRepository->persistAndFlush($this->user);
    }

}
