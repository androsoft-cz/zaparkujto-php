<?php

namespace App\Model\Orm;

use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Repository\Repository;

abstract class AbstractRepository extends Repository
{

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->getMapper()->builder();
    }


    /**
     * @param $id
     */
    public function delete($id)
    {
        $place = $this->getById($id);
        $place->deletedAt = new \DateTime;
        $this->persistAndFlush($place);
    }

}
