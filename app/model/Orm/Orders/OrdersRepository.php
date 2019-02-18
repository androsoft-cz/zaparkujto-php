<?php

namespace App\Model\Orm\Orders;

use App\Model\Orm\AbstractRepository;

class OrdersRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Order::class,
        ];
    }
}
