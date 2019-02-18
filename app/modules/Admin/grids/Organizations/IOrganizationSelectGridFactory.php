<?php

namespace App\Modules\Admin\Grids\Organizations;

interface IOrganizationSelectGridFactory
{
    public function create(): OrganizationSelectGrid;
}
