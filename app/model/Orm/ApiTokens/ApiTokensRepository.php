<?php

namespace App\Model\Orm\ApiTokens;

use App\Model\Orm\AbstractRepository;

class ApiTokensRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            ApiToken::class,
        ];
    }
}
