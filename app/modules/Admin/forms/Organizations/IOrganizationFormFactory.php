<?php

namespace App\Modules\Admin\Forms\Organizations;

interface IOrganizationFormFactory
{

    /**
     * @return OrganizationForm
     */
    public function create();
}
