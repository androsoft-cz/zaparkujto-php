<?php

namespace App\Modules\Admin\Forms\TimeWindows;

interface ITimeWindowFormFactory
{

    /**
     * @return TimeWindowForm
     */
    public function create();
}
