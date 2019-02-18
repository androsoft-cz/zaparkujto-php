<?php

namespace App\Modules\Driver\Forms\Profiles;

interface IProfileFormFactory
{
    /**
     * @return ProfileForm
     */
    public function create();

}
