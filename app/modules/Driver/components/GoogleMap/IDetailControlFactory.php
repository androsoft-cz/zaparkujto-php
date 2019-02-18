<?php

namespace App\Modules\Driver\Components\GoogleMap;

use App\Model\Orm\Places\Place;

interface IDetailControlFactory
{

    /**
     * @param Place $place
     * @return DetailControl
     */
    public function create(Place $place);

}
