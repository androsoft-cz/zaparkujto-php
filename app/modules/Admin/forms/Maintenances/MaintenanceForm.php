<?php

namespace App\Modules\Admin\Forms\Maintenances;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Maintenances\Maintenance;
use App\Model\Orm\Maintenances\MaintenancesRepository;
use App\Model\Orm\Places\PlacesRepository;

final class MaintenanceForm extends BaseForm
{

    /** @var MaintenancesRepository */
    public $maintenancesRepository;

    /** @var PlacesRepository */
    public $placesRepository;

    public function __construct(
        \Kdyby\Translation\Translator $translator,
        MaintenancesRepository $maintenancesRepository,
        PlacesRepository $useofPlacesRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->maintenancesRepository = $maintenancesRepository;
        $this->placesRepository = $useofPlacesRepository;

        $this->addGroup('');
        $this->addText('maintenancedAt', 'forms.maintenance.date')
            ->setRequired('forms.maintenance.rule.date');
        $this->addTextArea('maintenanceNote', 'forms.maintenance.text');
        $this->addText('maintenanceResult', 'forms.maintenance.result');
        $this->addHidden('placeId');
        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.maintenance.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm()
    {
        $values = $this->getValues();

        if ($values->id != '') { //update
            /** @var Maintenance $maintenance */
            $maintenance = $this->maintenancesRepository->getById($values->id);
        } else {   //insert
            $place = $this->placesRepository->getById($values->placeId);

            /** @var Maintenance $maintenance */
            $maintenance = new Maintenance();
            $maintenance->place = $place;
            $maintenance->user = $this->getPresenter()->myUser;
        }

        $maintenance->maintenancedAt = $values->maintenancedAt;
        $maintenance->maintenanceNote = $values->maintenanceNote;
        $maintenance->maintenanceResult = $values->maintenanceResult;

        $this->maintenancesRepository->persistAndFlush($maintenance);
    }

}
