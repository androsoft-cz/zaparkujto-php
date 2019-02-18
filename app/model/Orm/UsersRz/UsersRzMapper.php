<?php

namespace App\Model\Orm\UsersRz;

use App\Model\Orm\AbstractMapper;
use App\Model\Orm\Users\User;

class UsersRzMapper extends AbstractMapper
{

    /**
     * @return array
     */
    public function findAllUserRzs(User $user)
    {
        $builder = $this->builder()
            ->select('rz')
            ->where('user_id = %i', $user->id);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetchPairs(NULL, 'rz');
        if ($row) {
            return (array) $row;
        }

        return [];
    }


    /**
     * @param array
     * @return array
     */
    public function findRzsToDelete(User $user, array $rsz)
    {
        $builder = $this->builder()
            ->select('id, rz')
            ->where('user_id = %i', $user->id)
            ->andWhere('rz NOT IN %s[]', $rsz);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetchAll();
        if ($row) {
            return $row;
        }

        return [];
    }

}
