<?php

namespace App\Model\Orm\ApiTokens;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;

/**
 * @property int $id                        {primary}
 * @property string $token
 * @property string $type                   {enum self::TYPE_*}
 * @property DateTime $createdAt            {default now}
 * @property DateTime $expiredAt
 *
 * @property User $user                     {m:1 User::$apiTokens}
 */
class ApiToken extends AbstractEntity
{

    const TYPE_ACCESS = 'access';
    const TYPE_REFRESH = 'refresh';
}
