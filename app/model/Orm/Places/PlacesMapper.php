<?php

namespace App\Model\Orm\Places;

use App\Model\Orm\AbstractMapper;
use Nextras\Dbal\Result\Result;

class PlacesMapper extends AbstractMapper
{
    /**
     * @param array $conditions
     * @return Result|NULL
     * @throws \Nextras\Dbal\QueryException
     */
    public function findUniqueByStreet(array $conditions): ?Result
    {
        $builder = $this->builder()
            ->select('DISTINCT [street_name]');

        if (isset($conditions['organization'])) {
            $builder->andWhere('[organization_id] = %i', $conditions['organization']);
        }

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }

    public function isPlaceMember($id, $username): ?Result
    {
        $builder = $this->builder()
            ->select('COUNT(id) AS is_member')
            ->andWhere('[id] = %i', $id)
            ->andWhere('Locate(%s, members)', $username);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }

    /**
     * @param float $lat
     * @param float $lng
     * @return Result|NULL
     * @throws \Nextras\Dbal\QueryException
     */
    public function findByGps(float $lat, float $lng): ?Result
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        // p.location_radius is set in meters in application administration
        $builder = $this->builder()
            ->select('[p.*], GET_DISTANCE(%f, %f, [p.lat], [p.lng], \'KM\') AS [distance_in_km]', $lat, $lng)
            ->from('[places]', 'p')
            ->where('[p.location_radius] / 1000 > GET_DISTANCE(%f, %f, [p.lat], [p.lng], \'KM\')', $lat, $lng)
            ->orderBy('distance_in_km');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    /**
     * Find places to check by police
     *
     * @param array $conditions
     * @return Result|NULL
     * @throws \Nextras\Dbal\QueryException
     */
    public function findPlacesToCheck(array $conditions): ?Result
    {
        $builder = $this->builder()->select('places.id, places.name, places.description, places.street_name, places.identifier, places.lat, places.lng, r.from, r.to, r.rz, o.paid_at, o.price, o.state, o.vs');

        $builder->leftJoin('places', 'reservations', 'r', 'places.id = r.place_id');
        $builder->leftJoin('r', 'orders', 'o', 'r.order_id = o.id');

        if (isset($conditions['organization'])) {
            $builder->andWhere('[organization_id] = %i', $conditions['organization']);
        }

        if (isset($conditions['streetName'])) {
            $builder->andWhere('[street_name] = %s', $conditions['streetName']);
        }

        if (isset($conditions['checkTime'])) {
            $builder->andWhere('[r.from] = %d', $conditions['checkTime']);
        }

        $builder->andWhere('places.deleted_at IS null');
        $builder->andWhere('o.state = 3');

        $builder->addOrderBy('r.from');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    /**
     * @param array $conditions
     * @return Result
     * @throws \Nextras\Dbal\QueryException
     */
    public function search(array $conditions): Result
    {
        $builder = $this->builder()
            ->from('[places]', 'p')
            ->select('[p.*]')
            ->andWhere('[p.deleted_at] IS NULL');

        if (isset($conditions['lat'], $conditions['lng'])) {
            $lat = (float) $conditions['lat'];
            $lng = (float) $conditions['lng'];
            $builder->addSelect('GET_DISTANCE(%f, %f, [p.lat], [p.lng], \'KM\') AS distance', $lat, $lng);
            $builder->having('[distance] IS NOT NULL');
        } else {
            $builder->addSelect('0 AS distance');
        }

        return $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );
    }


    /**
     * @param array $conditions
     * @return Result
     * @throws \Nextras\Dbal\QueryException
     */
    public function searchOne(array $conditions): Result
    {
        $builder = $this->builder()
            ->from('[places]', 'p')
            ->select('[p.*]')
            ->andWhere('[p.deleted_at] IS NULL')
            ->andWhere('[p.id] = %i', $conditions['id']);

        return $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );
    }

    public function getCapacityOfPlaces($organizationId)
    {
        $builder = $this->builder()
            ->select('SUM(capacity) AS total')
            ->where('places.organization_id = %i', $organizationId);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetch();

        if ($row && $row->total) {
            return $row->total;
        }

        return 0;
    }
}
