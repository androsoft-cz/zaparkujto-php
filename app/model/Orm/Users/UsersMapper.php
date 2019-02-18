<?php

namespace App\Model\Orm\Users;

use App\Model\Orm\AbstractMapper;
use Nextras\Dbal\Result\Result;

class UsersMapper extends AbstractMapper
{

    /**
     * @param string $username
     * @param int $organizationId
     * @param string[] $roles
     * @param int $limit
     * @return Result|NULL
     */
    public function findByUsernameLike($username, $organizationId, array $roles, $limit)
    {
        $builder = $this->builder()
            ->select('*')
            ->where('[username] LIKE %_like_', $username)
            ->andWhere('[organization_id] = %i', $organizationId)
            ->andWhere('users.role IN %s[]', $roles)
            ->limitBy($limit);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }


    /**
     * @param int
     * @return int
     */
    public function getTotalUsers($organizationId, \DateTime $from = NULL, \DateTime $to = NULL)
    {
        $builder = $this->builder()
            ->select('COUNT(users.id) AS total')
            ->where('users.organization_id = %i', $organizationId);

        if ($from) {
            $builder = $builder->andWhere('DATE(users.created_at) >= %any', $from->format('Y-m-d'));
        }

        if ($to) {
            $builder = $builder->andWhere('DATE(users.created_at) <= %any', $to->format('Y-m-d'));
        }

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


    /**
     * @param int
     * @return array
     */
    public function getUsersPerYearMonth($organizationId, \DateTime $from, \DateTime $to)
    {
        $builder = $this->builder()
            ->select('COUNT(users.id) AS total, DATE_FORMAT(users.created_at, "%%Y-%%m") AS dateGroup')
            ->where('users.organization_id = %i', $organizationId)
            ->andWhere('DATE(users.created_at) >= %any', $from->format('Y-m-d'))
            ->andWhere('DATE(users.created_at) <= %any', $to->format('Y-m-d'))
            ->groupBy('DATE_FORMAT(users.created_at, "%%Y-%%m")')
            ->orderBy('DATE_FORMAT(users.created_at, "%%Y-%%m")');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result->fetchPairs('dateGroup');
    }


    /**
     * @param int $placeId
     * @param int|NULL $timestamp
     * @return Result|NULL
     */
    public function findReservedByPlaceId($placeId, $timestamp)
    {
        $weekDay = (int) date('N', $timestamp);
        $hours = (int) date('H', $timestamp);
        $minutes = (int) date('i', $timestamp);
        $time = $hours * 60 + $minutes;

        $builder = $this->builder()
            ->select('[users].*')
            ->from('users')
            ->leftJoin('users', 'time_windows', 'tw', 'tw.user_id = users.id AND tw.deleted_at IS NULL AND tw.weekday = %i AND tw.begin < %i AND tw.end > %i', $weekDay, $time, $time)
            ->where('users.place_id = %i', $placeId)
            ->andWhere('users.manual_shareable = %i', 0)
            ->andWhere('tw.id IS NULL');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result;
    }

}
