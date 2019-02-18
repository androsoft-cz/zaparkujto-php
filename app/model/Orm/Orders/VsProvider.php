<?php

namespace App\Model\Orm\Orders;

use App\Model\Orm\Reservations\Reservation;

final class VsProvider
{
    /** @var OrdersMapper */
    private $mapper;


    /**
     * @param OrdersMapper $mapper
     */
    public function __construct(OrdersMapper $mapper)
    {
        $this->mapper = $mapper;
    }


    /**
     * @return string
     */
    public function get(Order $order): string
    {
        /** @var Reservation $reservation */
        $reservation = $order->reservations->get()->fetch();
        $organizationId = $reservation->place->organization->vsPrefix;
        return $this->mapper->getNextVs($organizationId);
    }
}
