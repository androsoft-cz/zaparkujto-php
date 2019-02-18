<?php

namespace App\Modules\Base\Forms\Security;

interface ISignInFormFactory
{

    /**
     * @return SignInForm
     */
    public function create();
}
