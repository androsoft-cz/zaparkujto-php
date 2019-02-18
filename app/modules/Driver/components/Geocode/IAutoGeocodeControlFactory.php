<?php

namespace App\Modules\Driver\Components\Geocode;

interface IAutoGeocodeControlFactory
{

    /**
     * @return AutoGeocodeControl
     */
    public function create();

}
