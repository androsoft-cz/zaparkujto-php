<?php

namespace App\Modules\Driver\Forms\Order;

use App\Model\Orm\Places\Place;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use Nette\Application\UI\Presenter;

interface IReservationFormFactory
{
    /**
     * @param Place $place
     * @param UseofPlaces $useofPlace
     * @param User $user
     * @param Presenter $presenter
     * @return ReservationForm
     */
    public function create(Place $place, UseofPlaces $useofPlace, User $user, Presenter $presenter);

}
