<?php

namespace App\Modules\Admin\Forms\Positions;

interface IPositionFormFactory
{
    /**
     * @return PositionForm
     */
    public function create();
}
