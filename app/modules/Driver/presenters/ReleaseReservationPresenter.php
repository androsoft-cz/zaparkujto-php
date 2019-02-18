<?php

namespace App\Modules\Driver;

use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Exceptions\Runtime\Reservation\TokenException;
use App\Model\Facade\ReleaseReservationFacade;
use App\Model\Orm\ReservationTokens\ReservationToken;

final class ReleaseReservationPresenter extends BasePresenter
{

    /** @var ReleaseReservationFacade @inject */
    public $releaseReservationFacade;

    /** @var ReservationToken */
    private $token;

    /**
     * @param string $token
     */
    public function actionRelease($token)
    {
        $this->token = $this->validateToken($token);

        try {
            $this->releaseReservationFacade->releaseReservation($this->token->reservation);
            $this->flashMessage('Rezervace byla uvolněna, děkujeme za využití našeho systému.', 'success');
        } catch (InvalidStateException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        } catch (\Exception $e) {
            $this->flashMessage('Bohužel se nepodařilo uvolnit rezervaci. Zkuste to prosím znovu.', 'danger');
        }

        $this->redirect(':Driver:Home:default');
    }

    /**
     * HELPERS *****************************************************************
     */

    /**
     * @param string $token
     * @return ReservationToken
     */
    protected function validateToken($token)
    {
        try {
            $token = $this->releaseReservationFacade->validateToken($token);
        } catch (TokenException $e) {
            switch ($e->getCode()) {
                case TokenException::NOT_FOUND:
                    $this->flashMessage('Bohužel takový token neexistuje');
                    break;
                case TokenException::EXPIRED:
                    $this->flashMessage('Bohužel token už není platný');
                    break;
                case TokenException::APPLIED:
                    $this->flashMessage('Bohužel token už byl uplatněn');
                    break;
            }

            $this->redirect(':Driver:Home:default');
        }

        return $token;
    }
}
