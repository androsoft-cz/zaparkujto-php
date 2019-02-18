<?php

namespace App\Modules\Admin\Grids\Inspections;

interface IInspectionsGridFactory
{

    /**
     * @return InspectionsGrid
     */
    public function create();
}
