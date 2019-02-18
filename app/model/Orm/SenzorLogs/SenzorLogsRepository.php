<?php

namespace App\Model\Orm\SenzorLogs;

use App\Model\Orm\AbstractRepository;

class SenzorLogsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            SenzorLog::class,
        ];
    }

}
