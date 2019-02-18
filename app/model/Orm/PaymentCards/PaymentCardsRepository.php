<?php

namespace App\Model\Orm\PaymentCards;

use App\Model\Orm\AbstractRepository;

class PaymentCardsRepository extends AbstractRepository
{
    public static function getEntityClassNames(): array
    {
        return [
            PaymentCard::class,
        ];
    }
}
