<?php

namespace App\Model\Orm\Positions;

use App\Model\Orm\AbstractMapper;

class PositionsMapper extends AbstractMapper
{
    public function countFreeSenzors($id):int
    {
        $builder = $this->builder()
            ->select('COUNT(*) AS free_senzors')
            ->andWhere('[place_id] = %i', $id)
            ->andWhere('[senzor_status] = %i', 0);

        $freeSenzors = $this->connection->queryArgs($builder->getQuerySql(), $builder->getQueryParameters())->fetch();
        $result = $freeSenzors->free_senzors;

        return $result;
    }
}
