<?php

namespace App\Model\Orm\TimeWindows;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Users\User;
use DateInterval;
use Nette\Utils\DateTime;

/**
 * @property int $id                                        {primary}
 * @property int $weekday
 * @property DateInterval $begin
 * @property DateInterval $end
 * @property DateTime|NULL $deletedAt
 *
 * @property User $user                                {m:1 User::$timeWindows}
 */
class TimeWindow extends AbstractEntity
{

}
