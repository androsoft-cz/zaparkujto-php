<?php

namespace App\Model\Orm\Maintenances;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;

/**
 * @property int $id                            {primary}
 * @property DateTime|NULL $maintenancedAt
 * @property string|NULL $maintenanceNote
 * @property string|NULL $maintenanceResult
 * @property DateTime|NULL $deletedAt
 *
 * @property User $user                         {m:1 User::$maintenances}
 * @property Place $place                       {m:1 Place::$maintenances}
 */
class Maintenance extends AbstractEntity
{

}
