<?php

namespace App\Modules\Admin\Forms\Inspections;

interface IInspectionFormFactory
{

    /**
     * @return InspectionForm
     */
    public function create();
}
