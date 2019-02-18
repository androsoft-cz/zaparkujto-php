<?php

namespace App\Modules\Admin;

use App\Model\Orm\Maintenances\Maintenance;
use App\Model\Orm\Maintenances\MaintenancesRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Modules\Admin\Forms\Maintenances\IMaintenanceFormFactory;
use App\Modules\Admin\Grids\Maintenances\IMaintenancesGridFactory;
use App\Modules\Admin\Grids\Maintenances\MaintenancesGrid;

class MaintenancesPresenter extends SecurePresenter
{

    /** @var IMaintenancesGridFactory @inject */
    public $gridFactory;

    /** @var IMaintenanceFormFactory @inject */
    public $formFactory;

    /** @var MaintenancesRepository @inject */
    public $repository;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.maintenances.title');
    }


    public function actionDefault()
    {
        /** @var MaintenancesGrid $grid */
        $grid = $this['grid'];
        $grid->setModelWithFilter($this->myUserData['org_id']);
    }


    public function actionAdd($id)
    {
        /** @var Place $place */
        $place = $this->placesRepository->getById($id);
        if (!$place) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($place->organization);

        $defaults = [
            'userId' => $this->myUserData['id'],
            'placeId' => $id,
        ];
        $this->getComponent('form')->setDefaults($defaults);
    }

    public function actionDelete($id)
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->repository->getById($id);
        if (!$maintenance) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($maintenance->place->organization);

        $this->repository->delete($id);
        $this->redirect('default');
    }

    public function actionDetail($id)
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->repository->getById($id);
        if (!$maintenance) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($maintenance->place->organization);

        $this->getComponent('form')->setDefaults($maintenance->toArray());
    }

    public function createComponentGrid()
    {
        return $this->gridFactory->create();
    }

    public function createComponentForm()
    {
        $form = $this->formFactory->create();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('default');
        };

        return $form;
    }
}
