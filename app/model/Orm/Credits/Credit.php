<?php

namespace App\Model\Orm\Credits;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;

/**
 * @property int $id                        {primary}
 * @property int $price
 * @property int $movementType              {enum self::MOVEMENT_TYPE_*}
 * @property DateTime $createdAt            {default now}
 * @property DateTime|NULL $deletedAt
 *
 * @property User $user                     {m:1 User::$credits}
 * @property Order|NULL $order              {m:1 Order::$credits}
 */
class Credit extends AbstractEntity
{

    // Dobití kreditu přes platební bránu
    const MOVEMENT_TYPE_CHARGE_BY_GATE = 1;
    // Dobití kreditu nárokem z města
    const MOVEMENT_TYPE_CHARGE_BY_CLAIM = 2;
    // Refundace, tedy vrácení kreditu
    const MOVEMENT_TYPE_REFUND = 3;
    // Platba parkování kreditem
    const MOVEMENT_TYPE_COSTS_PARKING = 11;

}
