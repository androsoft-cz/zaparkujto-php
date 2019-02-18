<?php

namespace App\Modules\Rpmapi;

use App\Model\Exceptions\Logical\RequestedResourceIsNotAvaiableException;
use App\Model\Facade\ReleaseReservationFacade;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use Nette\Http\Response;

class ReleasePresenter extends SecurePresenter
{

    /** @var ReservationsRepository @inject */
    public $reservationsRepository;

    /** @var  ReleaseReservationFacade @inject */
    public $releaseReservationFacade;

    public function actionCreate($data)
    {
        try {
            $d = json_decode($data);
            if (!$d) {
                $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
            }

            if (!$d->reservation_id) {
                $this->sendError('Reservation_id is not set. Call me, baby!', Response::S400_BAD_REQUEST);
            }

            if (!$d->released_from) {
                $this->sendError('Released_from is not set. Call me, baby!', Response::S400_BAD_REQUEST);
            }

            try {
                /** @var Reservation $reservation */
                $reservation = $this->reservationsRepository->getById($d->reservation_id);
                if (!$reservation) {
                    $this->sendError('Reservation is not found.', Response::S400_BAD_REQUEST);
                }

                /** @var Reservation $refunded_reservation */
                $refunded_reservation = $this->releaseReservationFacade->releaseReservation($reservation);
            } catch (RequestedResourceIsNotAvaiableException $e) {
                $this->sendError($e->getMessage(), Response::S409_CONFLICT);
            } catch (InvalidStateException $e) {
                $this->sendError($e->getMessage(), Response::S500_INTERNAL_SERVER_ERROR);
            } catch (\Exception $e) {
                $this->sendError('Bohužel se nepodařilo uvolnit rezervaci. Zkuste to prosím znovu. ', Response::S409_CONFLICT, $e->getMessage());
            }

            $results = [
                'id' => $refunded_reservation->id,
                'price' => $refunded_reservation->price,
                'origin_price' => $refunded_reservation->originPrice,
                'from' => $refunded_reservation->from,
                'to' => $refunded_reservation->to,
                'state' => $refunded_reservation->state,
                'place_identifier' => $refunded_reservation->place->identifier,
                'place_name' => $refunded_reservation->place->name,
                'place_street_name' => $refunded_reservation->place->streetName,
            ];

            $this->sendApiResponse($results);

        } catch (AuthenticationException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
