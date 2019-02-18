<?php

namespace App\Modules\Driver;

use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Modules\Driver\Forms\PasswordReset\PasswordResetForm;
use App\Modules\Driver\Forms\PasswordReset\IPasswordResetFormFactory;
use Nette\Application\BadRequestException;

class PasswordResetPresenter extends BasePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var IPasswordResetFormFactory @inject */
    public $formFactory;

    /** @var User */
    private $user;


    /**
     * @param string $token
     * @throws BadRequestException
     */
    public function actionReset($token)
    {
        $this->user = $this->usersRepository->getByPasswordResetToken($token);

        if (!$this->user) {
            throw new BadRequestException("User with password reset token '$token' not found.");
        }

        if ($this->user->passwordResetTokenGeneratedAt->getTimestamp() < (time() - 24 * 60 * 60)) {
            throw new BadRequestException('User token exceeded.');
        }

        $this->template->user = $this->user;
    }


    /**
     * @return PasswordResetForm
     */
    public function createComponentForm()
    {
        $form = $this->formFactory->create($this->user);

        $form->onSuccess[] = function ($form) {
            $this->redirect(':Driver:PasswordReset:success');
        };

        return $form;
    }

}
