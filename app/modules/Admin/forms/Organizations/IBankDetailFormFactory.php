<?php

namespace App\Modules\Admin\Forms\Organizations;

interface IBankDetailFormFactory
{

    /**
     * @return BankDetailForm
     */
    public function create();
}
