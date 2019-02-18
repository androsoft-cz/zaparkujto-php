<?php

namespace App\Model\Orm\Organizations;

use App\Model\Orm\AbstractRepository;

/**
 * @property-read OrganizationsMapper $mapper
 */
class OrganizationsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Organization::class,
        ];
    }


    /**
     * @param $slug
     * @return Organization|NULL
     */
    public function getBySlug($slug)
    {
        return $this->findBy(['slug' => $slug])->fetch();
    }

}
