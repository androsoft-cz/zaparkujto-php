<?php

namespace App\Modules\Driver\Forms\PasswordReset;

use App\Model\Orm\Users\User;

interface IPasswordResetFormFactory
{
    /**
     * @param User $user
     * @return PasswordResetForm
     */
    public function create(User $user);

}
