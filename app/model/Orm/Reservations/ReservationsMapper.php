<?php

namespace App\Model\Orm\Reservations;

use App\Model\Orm\AbstractMapper;
use App\Model\Orm\Orders\Order;
use Nette\Utils\DateTime;
use Nextras\Dbal\Result\Result;

class ReservationsMapper extends AbstractMapper
{
    /**
     * Return concurrent running reservations
     *
     * @throws \Nextras\Dbal\QueryException
     */
    public function findConcurrences(array $parameters): ?Result
    {
        $builder = $this->builder()
            ->from('[reservations]', 'r')
            ->andWhere('[r.place_id] = %i', $parameters['place'])
            ->andWhere(
                '
                ([r.from] <= %dt AND [r.to] >= %dt)
                OR
                ([r.from] <= %dt AND [r.to] >= %dt)
                OR
                ([r.from] > %dt AND [r.to] < %dt)
                ',
                $parameters['from'], $parameters['from'],
                $parameters['to'], $parameters['to'],
                $parameters['from'], $parameters['to']
            );

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    public function findLikeRz(string $rz): ?Result
    {
        $builder = $this->builder()
            ->from('reservations', 'r')
            ->where('[r.rz] LIKE %_like_', $rz);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    public function findByTime(int $placeId, DateTime $time): ?Result
    {
        $builder = $this->builder()
            ->from('[reservations]', 'r')
            ->andWhere('[r.place_id] = %i', $placeId)
            ->andWhere('[r.from] <= %dt', $time)
            ->andWhere('[r.to] >= %dt', $time);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    public function getOrganizationIncome(int $organizationId, array $orderStates = [Order::STATE_PAID])
    {
        $builder = $this->builder()
            ->select('SUM(reservations.price) AS income')
            ->leftJoin('reservations', 'places', 'places', 'places.id = reservations.place_id')
            ->leftJoin('reservations', 'orders', 'orders', 'orders.id = reservations.order_id')
            ->where('orders.state IN %any', $orderStates)
            ->andWhere('places.organization_id = %i', $organizationId);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetch();

        if ($row && $row->income) {
            return $row->income;
        }

        return 0;
    }

    public function getReservationsPerYearMonth(int $organizationId, \DateTime $from, \DateTime $to, array $orderStates = [Order::STATE_PAID]): array
    {
        $builder = $this->builder()
            ->select('COUNT(reservations.id) AS total, DATE_FORMAT(reservations.created_at, "%%Y-%%m") AS dateGroup')
            ->leftJoin('reservations', 'places', 'places', 'places.id = reservations.place_id')
            ->leftJoin('reservations', 'orders', 'orders', 'orders.id = reservations.order_id')
            ->where('places.organization_id = %i', $organizationId)
            ->andWhere('orders.state IN %any', $orderStates)
            ->andWhere('DATE(reservations.created_at) >= %any', $from->format('Y-m-d'))
            ->andWhere('DATE(reservations.created_at) <= %any', $to->format('Y-m-d'))
            ->groupBy('DATE_FORMAT(reservations.created_at, "%%Y-%%m")')
            ->orderBy('DATE_FORMAT(reservations.created_at, "%%Y-%%m")');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result->fetchPairs('dateGroup');
    }
}
