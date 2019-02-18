<?php

namespace App\Model\Orm\PaymentGateways;

use App\Model\Orm\AbstractRepository;

class PaymentGatewaysRepository extends AbstractRepository
{
    public static function getEntityClassNames(): array
    {
        return [
            PaymentGateway::class,
        ];
    }
}
