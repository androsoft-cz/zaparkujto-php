<?php

namespace App\Modules\Driver;

use App\Modules\Base\Forms\Security\ISignInFormFactory;
use App\Modules\Base\Forms\Security\SignInForm;
use Nette\Security\AuthenticationException;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @var ISignInFormFactory @inject */
    public $signInFormFactory;


    /**
     * Formulář přihlášení uživatele
     *
     * @return SignInForm
     */
    public function createComponentSignInForm()
    {
        $form = $this->signInFormFactory->create();
        $form->onSuccess[] = [$this, 'processSignInForm'];

        return $form;
    }


    /**
     * @param SignInForm $form
     */
    public function processSignInForm(SignInForm $form)
    {
        try {
            $values = $form->values;
            if ($values->remember) {
                $this->user->setExpiration('+ 14 days', FALSE);
            } else {
                $this->user->setExpiration('+ 20 minutes', TRUE);
            }

            $this->user->login($values->username, $values->password);
            $this->redirect('Home:');
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }
    }


    public function actionOut()
    {
        $this->getUser()->logout(TRUE);
        $this->flashMessage('flashmessages.logout');
        $this->redirect('in');
    }

}
