<?php

namespace App\Model\Orm\Reservations;

use App\Model\Orm\AbstractRepository;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use Nette\Utils\DateTime;
use Nextras\Orm\Collection\ICollection;

/**
 * @property ReservationsMapper $mapper
 */
class ReservationsRepository extends AbstractRepository
{
    public static function getEntityClassNames(): array
    {
        return [
            Reservation::class,
        ];
    }


    public function findByOrderId(int $orderId)
    {
        return $this->findBy(['order' => $orderId]);
    }


    public function findByTime(Place $place, DateTime $time)
    {
        $result = $this->mapper->findByTime($place->id, $time);
        return $this->mapper->toCollection($result);
    }


    public function findConcurrences(Place $place, DateTime $dateFrom, DateTime $dateTo)
    {
        $result = $this->mapper->findConcurrences([
            'place' => $place->id,
            'from' => $dateFrom,
            'to' => $dateTo,
        ]);

        $collection = $this->mapper->toCollection($result);
        $collection->findBy([
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

        return $collection;
    }


    public function findByRz(string $rz)
    {
        $result = $this->mapper->findLikeRz($rz);
        return $this->mapper->toCollection($result);
    }


    public function findByUserId(int $id)
    {
        return $this->findBy(['this->user->id' => $id]);
    }


    public function findByPlaceId(int $id)
    {
        return $this->findBy(['this->place->id' => $id]);
    }


    public function findBySmsZone(string $smsKeyword)
    {
        return $this->findBy(['this->place->smsKeyword' => $smsKeyword]);
    }


    public function findPresentByPlace(int $placeId): ICollection
    {
        $now = new DateTime();
        return $this->findByPlaceId($placeId)->findBy(['this->order->state' => Order::STATE_PAID, 'from<=' => $now, 'to>=' => $now]);
    }


    public function findPresentBySmsZone(string $smsKeyword): ICollection
    {
        $now = new DateTime();
        return $this->findBySmsZone($smsKeyword)->findBy(['this->order->state' => Order::STATE_PAID, 'this->order->paymentType' => Order::PAYMENT_TYPE_SMS, 'from<=' => $now, 'to>=' => $now]);
    }


    public function findPresentByRz(string $rz)
    {
        $now = new DateTime();
        return $this->findByRz($rz)->findBy(['this->order->state' => Order::STATE_PAID, 'from<=' => $now, 'to>=' => $now]);
    }


    public function findPresent(int $userId)
    {
        $now = new DateTime();
        return $this->findByUserId($userId)->findBy(['this->order->state' => Order::STATE_PAID, 'from<=' => $now, 'to>=' => $now]);
    }


    public function findLast(int $userId)
    {
        $now = new DateTime();
        return $this->findByUserId($userId)->findBy(['this->order->state' => Order::STATE_PAID, 'to<' => $now]);
    }


    public function findLastByRz(string $rz)
    {
        $now = new DateTime();
        return $this->findByRz($rz)->findBy(['this->order->state' => Order::STATE_PAID, 'to<' => $now]);
    }


    public function findLastByPlace(int $placeId)
    {
        $now = new DateTime();
        return $this->findByPlaceId($placeId)->findBy(['this->order->state' => Order::STATE_PAID, 'to<' => $now]);
    }


    public function findFutureByRz(string $rz)
    {
        $now = new DateTime();
        return $this->findByRz($rz)->findBy(['this->order->state' => Order::STATE_PAID, 'from>' => $now]);
    }


    public function findFuture(int $userId)
    {
        $now = new DateTime();
        return $this->findByUserId($userId)->findBy(['this->order->state' => Order::STATE_PAID, 'from>' => $now]);
    }


    public function findFutureByPlace(int $placeId)
    {
        $now = new DateTime();
        return $this->findByPlaceId($placeId)->findBy(['this->order->state' => Order::STATE_PAID, 'from>' => $now]);
    }


    public function findWaitingByRz(string $rz)
    {
        return $this->findByRz($rz)->findBy(['this->order->state' => [Order::STATE_WAITING, Order::STATE_PROCESSING]]);
    }


    public function findWaiting(int $userId)
    {
        return $this->findByUserId($userId)->findBy(['this->order->state' => [Order::STATE_WAITING, Order::STATE_PROCESSING]]);
    }


    public function findWaitingByPlace(int $placeId)
    {
        return $this->findByPlaceId($placeId)->findBy(['this->order->state' => [Order::STATE_WAITING, Order::STATE_PROCESSING]]);
    }


    public function findCanceledByRz(string $rz)
    {
        return $this->findByRz($rz)->findBy(['this->order->state' => Order::STATE_STORNO]);
    }


    public function findCancelled(int $userId)
    {
        return $this->findByUserId($userId)->findBy(['this->order->state' => Order::STATE_STORNO]);
    }
}
