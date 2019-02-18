<?php

namespace App\Modules\Cron;

use App\Model\Facade\CreditFacade;
use App\Model\OrganizationRequest;

final class CreditPresenter extends BasePresenter
{

    /** @var CreditFacade @inject */
    public $facade;

    /** @var OrganizationRequest @inject */
    public $organizationRequest;

    /**
     * Send email notifications
     *
     * @return void
     */
    public function actionAllocate()
    {
        $this->organizationRequest->setOrganization(2);
        $this->facade->allocateCreditsToResidents(2, 50);

        $success = $errors = 0;
        $this->output(sprintf('Notifications: success (%d) / errors (%d).', $success, $errors));
        $this->flush();
    }

}
