<?php

namespace App\Model\Orm\TimeWindows;

use App\Model\Orm\AbstractMapper;
use Nextras\Dbal\Result\Result;

class TimeWindowsMapper extends AbstractMapper
{

    /**
     * @param int $userId
     * @return Result|NULL
     */
    public function findByUserSorted($userId)
    {
        $builder = $this->builder()
            ->select('*')
            ->from('time_windows')
            ->where('user_id = %i', $userId)
            ->andWhere('deleted_at is null')
            ->orderBy('weekday');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    /**
     * @param int $userId
     * @return Result|NULL
     */
    public function findCurrentByUser($userId)
    {
        $weekDay = (int) date('N');
        $hours = (int) date('H');
        $minutes = (int) date('i');
        $time = $hours * 60 + $minutes;

        $builder = $this->builder()
            ->select('*')
            ->from('time_windows')
            ->where('user_id = %i', $userId)
            ->andWhere('deleted_at is null')
            ->andWhere('weekday = %i', $weekDay)
            ->andWhere('begin < %i', $time)
            ->andWhere('end > %i', $time);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }
}
