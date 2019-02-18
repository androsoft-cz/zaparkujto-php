<?php

namespace App\Modules\Admin\Forms\Maintenances;

interface IMaintenanceFormFactory
{

    /**
     * @return MaintenanceForm
     */
    public function create();
}
