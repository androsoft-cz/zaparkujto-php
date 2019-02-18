<?php

namespace App\Modules\Admin;

use App\Model\Orm\Inspections\Inspection;
use App\Model\Orm\Inspections\InspectionsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Modules\Admin\Forms\Inspections\IInspectionFormFactory;
use App\Modules\Admin\Grids\Inspections\IInspectionsGridFactory;
use App\Modules\Admin\Grids\Inspections\InspectionsGrid;

class InspectionsPresenter extends SecurePresenter
{

    /** @var IInspectionsGridFactory @inject */
    public $gridFactory;

    /** @var IInspectionFormFactory @inject */
    public $formFactory;

    /** @var InspectionsRepository @inject */
    public $repository;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.inspections.title');
    }


    public function actionDefault()
    {
        /** @var InspectionsGrid $grid */
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
        /** @var Inspection $inspection */
        $inspection = $this->repository->getById($id);
        if (!$inspection) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($inspection->place->organization);

        $this->repository->delete($id);
        $this->redirect('default');
    }

    public function actionDetail($id)
    {
        /** @var Inspection $inspection */
        $inspection = $this->repository->getById($id);
        if (!$inspection) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($inspection->place->organization);

        $this->getComponent('form')->setDefaults($inspection->toArray());
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
