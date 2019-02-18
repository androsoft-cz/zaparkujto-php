<?php

namespace App\Model\Reservations;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;

class PlaceReservatorResult
{
    /** @var Reservation|NULL */
    public $reservation;

    /** @var Order|NULL */
    public $order;

    /** @var array|NULL */
    public $gatewayResult;
}
