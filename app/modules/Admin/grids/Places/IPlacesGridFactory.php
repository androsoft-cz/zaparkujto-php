<?php

namespace App\Modules\Admin\Grids\Places;

interface IPlacesGridFactory
{

    /**
     * @return PlacesGrid
     */
    public function create();
}
