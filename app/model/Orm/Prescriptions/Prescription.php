<?php

namespace App\Model\Orm\Prescriptions;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;

/**
 * @property int $id                                {primary}
 * @property int|NULL $payeeId
 * @property string $vs
 * @property int $amount
 * @property DateTime $reminderDate
 * @property string $reminderText
 * @property string $reminderNote
 * @property DateTime $createdAt                    {default now}
 *
 * @property User|ManyHasOne|NULL $user             {m:1 User::$prescriptions}
 */
class Prescription extends AbstractEntity
{

}
