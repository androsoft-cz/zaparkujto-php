<?php

namespace App\Modules\Admin\Grids\Users;

interface IUsersGridFactory
{

    /**
     * @return UsersGrid
     */
    public function create();
}
