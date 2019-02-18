<?php

namespace App\Model\Orm\SenzorLogs;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Positions\Position;
use Nette\Utils\DateTime;

/**
 * @property int $id                         {primary}
 * @property string $device
 * @property string $lat
 * @property string $lng
 * @property int|NULL $status                {enum Position::SENZOR_STATUS_*} {default Position::SENZOR_STATUS_UNDEFINED}
 * @property DateTime $createdAt
 *
 * @property Position $position              {m:1 Position::$senzorLogs}
 */
class SenzorLog extends AbstractEntity
{

}
