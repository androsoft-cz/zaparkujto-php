<?php

namespace App\Modules\Admin\Grids\Maintenances;

interface IMaintenancesGridFactory
{

    /**
     * @return MaintenancesGrid
     */
    public function create();
}
