<?php

namespace App\Modules\Admin\Grids\PlaceCards;

interface IPlaceCardsGridFactory
{

    /**
     * @return PlaceCardsGrid
     */
    public function create();
}
