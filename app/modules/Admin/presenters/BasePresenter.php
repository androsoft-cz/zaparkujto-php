<?php

namespace App\Modules\Admin;

use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Modules\Base\BasePresenter as Base;

/**
 * Base presenter for all admin presenters.
 */
abstract class BasePresenter extends Base
{
    /** @persistent */
    public $rootedOrgId; // pouze pro root uživatele aby mohl spravovat jakoukoliv organizaci

    /** @var array */
    public $myUserData;

    /** @var UsersRepository @inject */
    public $usersRepository;


    protected function startup()
    {
        parent::startup();

        if ($this->user->isLoggedIn()) {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);

            $rootedOrgId = $this->getParameter('rootedOrgId');
            $this->myUserData = [
                'org_id' => $user->organization ? $user->organization->id : $rootedOrgId,
                'id' => $this->user->id,
                'rooted_org_id' => $rootedOrgId
            ];
        } else {
            $this->myUserData = [
                'org_id' => NULL,
                'id' => NULL,
            ];
        }

        if (isset($this->rootedOrgId) && $this->user->isInRole('root')) {
            $this->myUserData['org_id'] = $this->rootedOrgId;
        }
    }


    protected function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.base.title');
    }
}
