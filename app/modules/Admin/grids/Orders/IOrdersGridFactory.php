<?php

namespace App\Modules\Admin\Grids\Orders;

interface IOrdersGridFactory
{

    /**
     * @return OrdersGrid
     */
    public function create();
}
