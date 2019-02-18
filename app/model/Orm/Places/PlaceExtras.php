<?php

namespace App\Model\Orm\Places;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use Nette\Utils\DateTime;
use Nextras\Orm\Collection\ICollection;

/**
 * @property-read $this Place
 */
trait PlaceExtras
{

    /**
     * @param Place $place
     * @return Reservation[]|ICollection
     */
    public function getReservations(Place $place)
    {
        return $place->reservations->get()->findBy([
            'this->order->state' => [
                Order::STATE_PAID,
                Order::STATE_PARTIALY_REFUNDED,
            ],
            'state' => [
                Reservation::STATE_NORMAL,
                Reservation::STATE_RELEASED,
                Reservation::STATE_EXTENDED,
                Reservation::STATE_RESERVED,
            ],
        ]);
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation[]|ICollection
     */
    public function getPreviousReservations(Place $place, DateTime $time)
    {
        return $this->getReservations($place)
            ->findBy([
                'from<' => $time,
                'to<' => $time,
            ])->orderBy('id', 'DESC');
    }

    /**
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getPreviousReservation(DateTime $time)
    {
        return $this->getPreviousReservations($this, $time)->fetch();
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getPreviousReservationFor(Place $place, DateTime $time)
    {
        return $this->getPreviousReservations($place, $time)->fetch();
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation[]|ICollection
     */
    public function getCurrentReservations(Place $place, DateTime $time)
    {
        return $this->getReservations($place)
            ->findBy([
                'from<=' => $time,
                'to>=' => $time,
            ]);
    }

    /**
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getCurrentReservation(DateTime $time)
    {
        return $this->getCurrentReservations($this, $time)->fetch();
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getCurrentReservationFor(Place $place, DateTime $time)
    {
        return $this->getCurrentReservations($place, $time)->fetch();
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation[]|ICollection
     */
    public function getNextReservations(Place $place, DateTime $time)
    {
        return $this->getReservations($place)
            ->orderBy('from')
            ->findBy([
                'from>=' => $time,
                'to>' => $time,
            ]);
    }

    /**
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getNextReservation(DateTime $time)
    {
        return $this->getNextReservations($this, $time)->fetch();
    }

    /**
     * @param Place $place
     * @param DateTime $time
     * @return Reservation|NULL
     */
    public function getNextReservationFor(Place $place, DateTime $time)
    {
        return $this->getNextReservations($place, $time)->fetch();
    }

}
