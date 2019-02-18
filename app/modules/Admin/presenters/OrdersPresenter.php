<?php

namespace App\Modules\Admin;

use App\Model\Facade\OrderFacade;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Modules\Admin\Grids\Orders\IOrdersGridFactory;
use App\Modules\Admin\Grids\Orders\OrdersGrid;

class OrdersPresenter extends SecurePresenter
{
    /** @var IOrdersGridFactory @inject */
    public $gridFactory;

    /** @var OrganizationsRepository @inject */
    public $organizationsRepository;

    /** @var OrderFacade @inject */
    public $orderFacade;


    public function startup()
    {
        parent::startup();
        $organization = $this->organizationsRepository->findById($this->myUserData['org_id'])->fetch();
        $this->checkOrganizationDataAccess($organization);
    }


    public function createComponentGrid(): Grids\Orders\OrdersGrid
    {
        return $this->gridFactory->create();
    }


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.orders.title');
    }


    public function actionDefault()
    {
        /** @var OrdersGrid $grid */
        $grid = $this['grid'];
        $grid->setModelWithFilter($this->myUserData['org_id']);
    }
}
