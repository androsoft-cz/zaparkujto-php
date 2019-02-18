<?php

namespace App\Model\Orm\Positions;

use App\Model\Orm\AbstractRepository;

class PositionsRepository extends AbstractRepository
{
    public function countFreeSenzors($id)
    {
        return $this->mapper->countFreeSenzors($id);
    }

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Position::class,
        ];
    }

}
