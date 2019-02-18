<?php

namespace App\Model\Orm\UsersRz;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;

/**
 * @property int $id                                {primary}
 * @property string $rz
 * @property DateTime $createdAt                    {default now}
 *
 * @property ManyHasOne|User|NULL $user             {m:1 User::$userRzs}
 */
class UserRz extends AbstractEntity
{

}
