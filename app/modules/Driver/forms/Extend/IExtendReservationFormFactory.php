<?php

namespace App\Modules\Driver\Forms\Extend;

interface IExtendReservationFormFactory
{

    /**
     * @return ExtendReservationForm
     */
    public function create();
}
