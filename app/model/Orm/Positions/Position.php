<?php

namespace App\Model\Orm\Positions;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Places\Place;
use App\Model\Orm\SenzorLogs\SenzorLog;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id                               {primary}
 * @property int|NULL $senzorStatus                {enum self::SENZOR_STATUS_*} {default self::SENZOR_STATUS_UNDEFINED}
 * @property DateTime|NULL $senzorStatusChangedAt
 * @property string $senzorNumber
 * @property string $senzorName
 *
 * @property Place $place                          {m:1 Place::$positions}
 * @property OneHasMany|SenzorLog[] $senzorLogs    {1:m SenzorLog::$position}
 */
class Position extends AbstractEntity
{
    const SENZOR_STATUS_FREE = 0;
    const SENZOR_STATUS_FULL = 1;
    const SENZOR_STATUS_UNDEFINED = NULL;
}
