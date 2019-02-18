<?php

namespace App\Modules\Driver;

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

        if (!$this->authorizator->isAllowed($role, $resource)) {
            $this->redirect('Sign:in');
        }
    }

}
