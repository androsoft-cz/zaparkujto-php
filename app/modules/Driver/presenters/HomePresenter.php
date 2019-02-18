<?php

namespace App\Modules\Driver;

class HomePresenter extends SecurePresenter
{

    public function actionDefault(): void
    {
        //TODO: refactor to parametersProvider or delete at all
        $moduleDisabled = $this->context->parameters['temporary']['disable_driver_module'] ?? TRUE;
        if ($moduleDisabled) {
            $this->redirect('Sign:in');
        }
    }
}
