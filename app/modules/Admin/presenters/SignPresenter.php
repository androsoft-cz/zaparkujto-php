<?php

namespace App\Modules\Admin;

use App\Modules\Admin\Grids\Organizations\IOrganizationSelectGridFactory;
use App\Modules\Base\Forms\Security\ISignInFormFactory;
use App\Modules\Base\Forms\Security\ISignUpFormFactory;
use App\Modules\Base\Forms\Security\SignInForm;
use App\Modules\Base\Forms\Security\SignUpForm;
use Nette\Security\AuthenticationException;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
    /** @var ISignUpFormFactory @inject */
    public $signUpFormFactory;

    /** @var ISignInFormFactory @inject */
    public $signInFormFactory;

    /** @var IOrganizationSelectGridFactory @inject */
    public $organizationSelectGridFactory;


    /**
     * Sign-up form factory.
     *
     * @return SignUpForm
     */
    public function createComponentSignUpForm()
    {
        $form = $this->signUpFormFactory->create();
        $form->onSuccess[] = [$this, 'processSignUpForm'];

        return $form;
    }


    /**
     * @param SignUpForm $form
     * @return SignUpForm
     */
    public function processSignUpForm(SignUpForm $form)
    {
        return $form;
    }


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


    public function actionOrganizationSelect(bool $isRoot)
    {
        if (!$isRoot) {
            $this->flashMessage('flashmessages.noOrganizationAssigned');
        }

        $this->template->isRoot = $isRoot;
    }


    public function createComponentOrganizationSelectGrid()
    {
        return $this->organizationSelectGridFactory->create();
    }


    public function actionSetRootedOrg($id)
    {
        $this->rootedOrgId = $id;
        $this->redirect('Home:default');
    }
}
