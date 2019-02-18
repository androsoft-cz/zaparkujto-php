<?php

namespace App\Modules\Base\Forms\Security;

interface ISignUpFormFactory
{

    /**
     * @return SignUpForm
     */
    public function create();
}
