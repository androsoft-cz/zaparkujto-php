<?php

namespace App\Modules\Admin\Grids\Position;

interface IPositionGridFactory
{
    /**
     * @return PositionGrid
     */
    public function create();
}
