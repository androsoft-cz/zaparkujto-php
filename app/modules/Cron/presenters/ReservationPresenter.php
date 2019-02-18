<?php

namespace App\Modules\Cron;

use App\Model\Facade\ReservationFacade;
use Tracy\Debugger;

final class ReservationPresenter extends BasePresenter
{

    /** @var ReservationFacade @inject */
    public $facade;

    /**
     * Cancel all reservation in state RESERVED, if time's up
     *
     * @return void
     */
    public function actionCancelReserved()
    {
        $reservations = $this->facade->getCancelableReservedReservations();
        $success = $errors = 0;

        foreach ($reservations as $reservation) {
            try {
                $this->facade->cancelReserved($reservation);
                $success++;
            } catch (\Exception $e) {
                Debugger::log($e);
                $errors++;
            }
        }

        $this->output(sprintf('Canceled: success (%d) / errors (%d).', $success, $errors));
        $this->flush();
    }

}
