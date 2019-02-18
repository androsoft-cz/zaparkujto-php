<?php

namespace App\Modules\Cron;

use App\Model\Facade\NotificationFacade;
use Tracy\Debugger;

final class EmailPresenter extends BasePresenter
{

    /** @var NotificationFacade @inject */
    public $facade;

    /**
     * Send email notifications
     *
     * @return void
     */
    public function actionNotification()
    {

        $reservations = $this->facade->getSoonExpireReservations();
        $success = $errors = 0;

        foreach ($reservations as $reservation) {
            try {
                $this->facade->sendSoonExpireNotification($reservation);
                $success++;
            } catch (\Exception $e) {
                Debugger::log($e);
                $errors++;
            }
        }

        $this->output(sprintf('Notifications: success (%d) / errors (%d).', $success, $errors));
        $this->flush();
    }

}
