<?php

namespace App\Modules\Admin\Grids\Organizations;

interface IOrganizationsGridFactory
{

    /**
     * @return OrganizationsGrid
     */
    public function create();
}
