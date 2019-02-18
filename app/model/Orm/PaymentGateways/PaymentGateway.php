<?php

namespace App\Model\Orm\PaymentGateways;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\PaymentCards\PaymentCard;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int                       $id                 {primary}
 * @property string                    $gateway            {enum self::GATEWAY_*}
 * @property string|NULL               $description
 * @property bool                      $testMode           {default false}
 * @property string                    $setting
 * @property bool                      $storeCard          {default true}
 * @property DateTime                  $createdAt          {default now}
 * @property DateTime|NULL             $deletedAt
 * @property OneHasMany|Organization[] $organizations      {1:m Organization::$paymentGateway}
 * @property OneHasMany|PaymentCard[]  $paymentCards       {1:m PaymentCard::$paymentGateway}
 */
class PaymentGateway extends AbstractEntity
{
    const GATEWAY_GOPAY = 'gopay';
    const GATEWAY_THEPAY = 'thepay';
    const GATEWAY_CSOB = 'csob';
}
