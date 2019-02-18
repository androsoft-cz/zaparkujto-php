<?php

namespace App\Modules\Driver;

use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UsersRzMapper;
use App\Modules\Driver\Forms\Profiles\IProfileFormFactory;
use App\Modules\Driver\Forms\Profiles\ProfileForm;

class ProfilePresenter extends SecurePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var UsersRzMapper @inject */
    public $usersRzMapper;

    /** @var IProfileFormFactory @inject */
    public $formFactory;


    public function actionDetail()
    {
        /** @var User $user */
        $user = $this->usersRepository->getById($this->user->id);
        $rzs = $this->usersRzMapper->findAllUserRzs($user);

        $defaults = $user->contact->toArray();
        $defaults['id'] = $user->id;
        $defaults['name'] = $user->name;
        $defaults['rz'] = implode(', ', $rzs);
        $defaults['creditAmount'] = $user->creditAmount;

        /** @var ProfileForm $form */
        $form = $this->getComponent('form');
        $form->setDefaults($defaults);
    }


    /**
     * @return ProfileForm
     */
    protected function createComponentForm()
    {
        /** @var ProfileForm $form */
        $form = $this->formFactory->create();
        $form->setOrganizationId($this->myUserData['org_id']);
        $form->onSuccess[] = function () {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('detail');
        };

        return $form;
    }

}
