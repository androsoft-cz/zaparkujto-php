<?php

namespace App\Modules\Admin;

use App\Model\Orm\Organizations\Organization;
use App\Model\Security\Authorizator;
use App\Modules;
use Nette\Application\ForbiddenRequestException;

abstract class SecurePresenter extends BasePresenter
{
    /** @var Authorizator @inject */
    public $authorizator;


    /**
     * Common template method
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.secure.title');
    }


    /**
     * Common check requirements
     *
     * @param mixed $element
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element) // @codingStandardsIgnoreLine
    {
        $this->checkAclRequirements();
    }


    /**
     * Checks ACL authorization
     */
    protected function checkAclRequirements()
    {
        $role = current($this->user->getRoles());
        $resource = $this->getName();

        if ($resource == 'Admin:Organizations') {
            $action = $this->getAction();

            if ($action == 'detail' || $action == 'editLongText') {
                $resource .= ':' . $action;
            }
        }

        if (!$this->authorizator->isAllowed($role, $resource)) {
            if (!$this->user->isLoggedIn()) {
                $this->redirect('Sign:in');
            } else {
                $this->redirect('Sign:in');
                throw new ForbiddenRequestException();
            }
        }
    }


    /**
     * @param Organization|int|NULL $organization
     * @throws ForbiddenRequestException
     */
    public function checkOrganizationDataAccess($organization)
    {
        $isRoot = $this->user->isInRole('root');

        if ($organization === NULL) {
            $this->redirect('Sign:organizationSelect', $isRoot);
        }

        if ($isRoot) {
            return;
        }

        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if ($this->myUserData['org_id'] != $organizationId) {
            throw new ForbiddenRequestException("Access to organization '$organizationId' denied.");
        }
    }
}
