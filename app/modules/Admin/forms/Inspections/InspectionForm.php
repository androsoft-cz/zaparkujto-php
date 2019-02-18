<?php

namespace App\Modules\Admin\Forms\Inspections;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Inspections\Inspection;
use App\Model\Orm\Inspections\InspectionsRepository;
use App\Model\Orm\Places\PlacesRepository;

final class InspectionForm extends BaseForm
{

    /** @var InspectionsRepository */
    public $inspectionsRepository;

    /** @var PlacesRepository */
    public $placesRepository;

    public function __construct(
        \Kdyby\Translation\Translator $translator,
        InspectionsRepository $inspectionsRepository,
        PlacesRepository $useofPlacesRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->inspectionsRepository = $inspectionsRepository;
        $this->placesRepository = $useofPlacesRepository;

        $this->addGroup('');
        $this->addText('inspectedAt', 'forms.inspection.date');
        $this->addTextArea('inspectionNote', 'forms.inspection.text');
        $this->addText('result', 'forms.inspection.result');
        $this->addHidden('placeId');
        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.inspection.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm()
    {
        $values = $this->getValues();

        if ($values->id != '') { //update
            /** @var Inspection $inspection */
            $inspection = $this->inspectionsRepository->getById($values->id);
        } else {   //insert
            $place = $this->placesRepository->getById($values->placeId);

            /** @var Inspection $inspection */
            $inspection = new Inspection();
            $inspection->place = $place;
            $inspection->user = $this->getPresenter()->myUser;
        }

        $inspection->inspectedAt = $values->inspectedAt;
        $inspection->inspectionNote = $values->inspectionNote;
        $inspection->result = $values->result;

        $this->inspectionsRepository->persistAndFlush($inspection);
    }

}
