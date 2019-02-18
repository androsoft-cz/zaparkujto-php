<?php

namespace App\Modules\Base\Forms\Security;

interface IPasswordFormFactory
{

    /**
     * @param int $userId
     * @return PasswordForm
     */
    public function create($userId);
}
