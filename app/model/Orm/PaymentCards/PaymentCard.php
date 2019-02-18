<?php

namespace App\Model\Orm\PaymentCards;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\PaymentGateways\PaymentGateway;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;

/**
 * @property int                       $id                {primary}
 * @property string                    $number
 * @property string|NULL               $name
 * @property DateTime                  $createdAt         {default now}
 * @property DateTime|NULL             $deletedAt
 * @property ManyHasOne|User           $user              {m:1 User::$paymentCards}
 * @property ManyHasOne|PaymentGateway $paymentGateway    {m:1 PaymentGateway::$paymentCards}
 * @property OneHasOne|Order           $referencePay      {1:1 Order, isMain=true, oneSided=true}
 * @property OneHasMany|Order[]        $orders            {1:m Order::$paymentCard}
 */
class PaymentCard extends AbstractEntity
{
}
