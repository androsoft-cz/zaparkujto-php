<?php

namespace App\Modules\Admin\Forms\UseofPlaces;

use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;

interface IUseofPlacesFormFactory
{
    public function create(Place $place, UseofPlaces $useofPlaces = NULL): UseofPlacesForm;
}
