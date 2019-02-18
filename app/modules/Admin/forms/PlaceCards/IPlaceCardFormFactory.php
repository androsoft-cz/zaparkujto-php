<?php

namespace App\Modules\Admin\Forms\PlaceCards;

interface IPlaceCardFormFactory
{

    /**
     * @return PlaceCardForm
     */
    public function create();

}
