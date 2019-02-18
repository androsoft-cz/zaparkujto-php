<?php

namespace App\Modules\Admin\Forms\Places;

interface IPlaceFormFactory
{

    /**
     * @return PlaceForm
     */
    public function create();
}
