<?php

namespace App\Model\Orm\TimeWindows;

use App\Model\Orm\AbstractRepository;
use Nextras\Orm\Collection\ICollection;

/**
 * @property-read TimeWindowsMapper $mapper
 */
class TimeWindowsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            TimeWindow::class,
        ];
    }


    /**
     * @param int $userId
     * @return TimeWindow[]|ICollection
     */
    public function findByUserSorted($userId)
    {
        $result = $this->mapper->findByUserSorted($userId);

        return $this->mapper->toCollection($result);
    }


    /**
     * @param int $userId
     * @return TimeWindow[]|ICollection
     */
    public function findCurrentByUser($userId)
    {
        $result = $this->mapper->findCurrentByUser($userId);
        return $this->mapper->toCollection($result);
    }

}
