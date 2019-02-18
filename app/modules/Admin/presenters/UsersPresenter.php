<?php

namespace App\Modules\Admin;

use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Modules\Admin\Forms\Users\IUserFormFactory;
use App\Modules\Admin\Forms\Users\UserForm;
use App\Modules\Admin\Grids\Users\IUsersGridFactory;

class UsersPresenter extends SecurePresenter
{

    /** @var IUsersGridFactory @inject */
    public $gridFactory;

    /** @var  IUserFormFactory @inject */
    public $formFactory;

    /** @var  UsersRepository @inject */
    public $repository;

    /** @var OrganizationsRepository @inject */
    public $organizationsRepository;

    public function beforeRender()
    {
        $organization = $this->organizationsRepository->findById($this->myUserData['org_id'])->fetch();
        $this->checkOrganizationDataAccess($organization);
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.users.title');
    }

    public function createComponentGrid()
    {
        return $this->gridFactory->create();
    }

    public function createComponentForm()
    {
        /** @var UserForm $form */
        $form = $this->formFactory->create();
        $form->setOrganizationId($this->myUserData['org_id']);
        $form->onSuccess[] = function () {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('default');
        };

        return $form;
    }

    public function actionDetail($id)
    {
        /** @var User $entity */
        $entity = $this->repository->getById($id);
        if (!$entity) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($entity->organization);

        $this->getComponent('form')->setDefaults($entity->toArray());
    }

    public function actionDefault($userType)
    {
        $grid = $this->getComponent('grid');
        if ($userType == 'drivers') {
            $grid->setModelWithFilter($this->myUserData['org_id'], ['user', 'admin', 'handyman', 'controlor', 'root']);
        } else {
            $grid->setModelWithFilter($this->myUserData['org_id'], ['admin', 'handyman', 'controlor', 'root']);
        }
    }

    public function actionDelete($id)
    {
        /** @var User $entity */
        $entity = $this->repository->getById($id);
        if (!$entity) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($entity->organization);

        $this->repository->delete($id);
        $this->redirect('default');
    }


}
