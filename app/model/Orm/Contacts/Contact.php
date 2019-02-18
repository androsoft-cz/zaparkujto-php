<?php

namespace App\Model\Orm\Contacts;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Users\User;

/**
 * @property int $id                                     {primary}
 * @property string|NULL $company
 * @property string|NULL $identificationNumber
 * @property string|NULL $taxIdentificationNumber
 * @property string|NULL $email
 * @property string|NULL $address1
 * @property string|NULL $address2
 * @property string|NULL $state
 * @property string|NULL $telephone
 *
 * @property Organization|NULL $organization             {1:1 Organization::$contact}
 * @property User|NULL $user                             {1:1 User::$contact}
 */
class Contact extends AbstractEntity
{

}
