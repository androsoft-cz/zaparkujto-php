<?php

namespace App\Modules\Driver;

use App\Modules\Base\BasePresenter as Base;

/**
 * Base presenter for all driver presenters.
 */
abstract class BasePresenter extends Base
{
    /** @var array */
    public $myUserData;


    protected function startup()
    {
        parent::startup();
        if (!isset($this->myUserData)) { //todo Předělat po dokončení prototypu
            $this->myUserData = [
                'org_id' => 1,
            ];
        }
    }

    protected function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.base.title');
        $this->template->showInspectorMenu = $this->user->isInRole('controlor');
    }

}
