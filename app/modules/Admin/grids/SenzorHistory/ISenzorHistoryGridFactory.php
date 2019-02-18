<?php

namespace App\Modules\Admin\Grids\SenzorHistory;

interface ISenzorHistoryGridFactory
{
    /**
     * @return SenzorHistoryGrid
     */
    public function create();
}
