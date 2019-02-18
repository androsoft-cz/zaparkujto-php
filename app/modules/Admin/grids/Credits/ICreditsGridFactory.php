<?php

namespace App\Modules\Admin\Grids\Credits;

interface ICreditsGridFactory
{

    /**
     * @return CreditsGrid
     */
    public function create();
}
