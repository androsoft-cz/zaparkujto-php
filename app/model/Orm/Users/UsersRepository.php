<?php

namespace App\Model\Orm\Users;

use App\Model\Orm\AbstractApplicationRepository;
use Nextras\Orm\Collection\ICollection;

/**
 * @property-read UsersMapper $mapper
 */
class UsersRepository extends AbstractApplicationRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            User::class,
        ];
    }


    /**
     * @param string $username
     * @return User|NULL
     */
    public function getByUsername($username)
    {
        return $this->getBy(['username' => $username]);
    }


    /**
     * @param string $resetToken
     * @return User|NULL
     */
    public function getByPasswordResetToken($resetToken)
    {
        return $this->getBy(['passwordResetToken' => $resetToken]);
    }


    /**
     * @param string $username
     * @param int $organizationId
     * @param string[] $roles
     * @param int $limit
     * @return User[]|ICollection
     */
    public function findByUsernameLike($username, $organizationId, array $roles, $limit)
    {
        $result = $this->mapper->findByUsernameLike($username, $organizationId, $roles, $limit);

        return $this->mapper->toCollection($result);
    }


    /**
     * @param int $placeId
     * @param int|NULL $timestamp
     * @return User[]|ICollection
     */
    public function findReservedShareable($placeId, $timestamp)
    {
        $result = $this->mapper->findReservedByPlaceId($placeId, $timestamp);

        return $this->mapper->toCollection($result);
    }

}
