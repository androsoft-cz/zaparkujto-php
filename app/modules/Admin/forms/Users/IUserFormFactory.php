<?php

namespace App\Modules\Admin\Forms\Users;

interface IUserFormFactory
{

    /**
     * @return UserForm
     */
    public function create();
}
