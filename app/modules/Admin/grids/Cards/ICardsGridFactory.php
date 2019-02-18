<?php

namespace App\Modules\Admin\Grids\Cards;

interface ICardsGridFactory
{

    /**
     * @return CardsGrid
     */
    public function create();
}
