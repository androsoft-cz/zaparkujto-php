<?php

namespace App\Modules\Admin\Forms\Cards;

interface ICardFormFactory
{

    /**
     * @return CardForm
     */
    public function create();
}
