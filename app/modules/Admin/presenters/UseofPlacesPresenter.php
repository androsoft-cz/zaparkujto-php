<?php

namespace App\Modules\Admin;

use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\UseofPlaces\UseofPlacesRepository;
use App\Modules\Admin\Forms\UseofPlaces\IUseofPlacesFormFactory;
use App\Modules\Admin\Forms\UseofPlaces\UseofPlacesForm;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nextras\Orm\Entity\AbstractEntity;

class UseofPlacesPresenter extends SecurePresenter
{
    /** @var UseofPlacesRepository @inject */
    public $useofPlacesRepository;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var IUseofPlacesFormFactory @inject */
    public $formFactory;

    /** @var UseofPlaces|NULL */
    private $useofPlaces;

    /** @var Place */
    private $place;


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('admin.useOfPlace');
    }


    /**
     * @param int $placeId
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionAdd($placeId)
    {
        $this->place = $this->placesRepository->getById($placeId);

        if (!$this->place) {
            throw new BadRequestException("Place '$placeId' not found.");
        }

        $this->checkOrganizationDataAccess($this->place->organization);
    }


    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail($id)
    {
        $this->useofPlaces = $this->useofPlacesRepository->getById($id);

        if (!$this->useofPlaces) {
            throw new BadRequestException("UseofPlaces '$id' not found.");
        }

        $this->checkOrganizationDataAccess($this->useofPlaces->place->organization);

        $this->place = $this->useofPlaces->place;

        $defaults = $this->useofPlaces->toArray(AbstractEntity::TO_ARRAY_RELATIONSHIP_AS_ID);

        $defaults['validDays'] = [];

        for ($i = 1; $i <= 7; $i++) {
            $defaults['validDays'][$i] = $this->useofPlaces->isValidDay($i);
        }

        $defaults['validFrom'] = isset($this->useofPlaces->validFrom) ? $this->useofPlaces->validFrom->format('%H:%I') : NULL;
        $defaults['validTo'] = isset($this->useofPlaces->validTo) ? $this->useofPlaces->validTo->format('%H:%I') : NULL;

        $hours = floor($this->useofPlaces->maxReservationTime / 60);
        $minutes = floor($this->useofPlaces->maxReservationTime % 60);
        $defaults['maxReservationTime'] = sprintf('%02d:%02d', $hours, $minutes);

        /** @var UseofPlacesForm $form */
        $form = $this->getComponent('form');
        $form->setDefaults($defaults);
    }


    /**
     * @param $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDelete($id)
    {
        /** @var UseofPlaces $useofPlaces */
        $useofPlaces = $this->useofPlacesRepository->getById($id);

        if (!$useofPlaces) {
            throw new BadRequestException("UseofPlaces '$id' not found.");
        }

        $this->checkOrganizationDataAccess($this->useofPlaces->place->organization);

        $place = $useofPlaces->place;

        $this->useofPlacesRepository->delete($id);
        $this->flashMessage('admin.msgRecordDeleted', 'success');
        $this->redirect(':Admin:Places:detail', ['id' => $place->id]);
    }


    /**
     * @return UseofPlacesForm
     */
    public function createComponentForm()
    {
        $form = $this->formFactory->create($this->place, $this->useofPlaces);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect(':Admin:Places:detail', ['id' => $this->place->id]);
        };

        return $form;
    }

}
