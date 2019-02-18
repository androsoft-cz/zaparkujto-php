<?php

namespace App\Modules\Admin\Grids\TimeWindows;

interface ITimeWindowsGridFactory
{

    /**
     * @return TimeWindowsGrid
     */
    public function create();
}
