<?php

namespace App\Model\Orm\Prescriptions;

use App\Model\Orm\AbstractRepository;

/**
 * @property PrescriptionsMapper $mapper
 */
class PrescriptionsRepository extends AbstractRepository
{
    public static function getEntityClassNames(): array
    {
        return [
            Prescription::class,
        ];
    }

    public function findByUserId(int $id)
    {
        return $this->findBy(['this->user->id' => $id]);
    }

}
