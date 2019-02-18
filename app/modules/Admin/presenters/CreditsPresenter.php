<?php

namespace App\Modules\Admin;

use App\Modules\Admin\Grids\Credits\ICreditsGridFactory;

class CreditsPresenter extends SecurePresenter
{

    /** @var ICreditsGridFactory @inject */
    public $gridFactory;


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.credits.title');
    }


    public function actionDefault()
    {
        $grid = $this->getComponent('grid');
        $grid->setModelWithFilter($this->myUserData['org_id']);
    }


    /**
     * @return Grids\Credits\CreditsGrid
     */
    protected function createComponentGrid()
    {
        return $this->gridFactory->create();
    }

}
