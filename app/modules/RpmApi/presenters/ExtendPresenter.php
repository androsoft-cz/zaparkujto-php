<?php

namespace App\Modules\Rpmapi;

use App\Model\Facade\ExtendReservationFacade;
use App\Model\Facade\OrderFacade;
use App\Model\Orm\Model;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\Users\User;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;

class ExtendPresenter extends SecurePresenter
{

    /** @var  ExtendReservationFacade @inject */
    public $extendReservationFacade;

    /** @var ReservationsRepository @inject */
    public $reservationsRepository;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var Model @inject */
    public $model;

    public function actionReadAll(array $query)
    {

        try {
            $reservation_id = isset($query['reservation_id']) ? $query['reservation_id'] : NULL;
            $extra = isset($query['extra']) ? $query['extra'] : NULL;
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }

        /** @var Reservation $reservation */
        $reservation = $this->model->reservations->getById($reservation_id);
        if (!$reservation) {
            $this->sendError('Reservation not found ', Response::S400_BAD_REQUEST);
        }

        try {
            /** @var Reservation $extendedReservation */
            $extendedReservation = $this->extendReservationFacade->createExtendReservation($reservation, $extra, FALSE);
        } catch (InvalidStateException $e) {
            Debugger::log($e);
            $this->sendError('Nepodařilo se neouzove prodloužit rezervaci.', Response::S400_BAD_REQUEST);

        } catch (InvalidReservationStateException $e) {
            Debugger::log($e);
            if ($e->state === Reservation::STATE_EXTENDED) {
                $this->sendError('Tuto rezervaci nelze nouzově prodloužít, ovlivnila by tím jinou nouzově prodlouženou rezervaci.', Response::S400_BAD_REQUEST);
            } else {
                $this->sendError('Tuto rezervaci nelze nouzově prodloužit', Response::S400_BAD_REQUEST);
            }
        } catch (AbortException $e) {
            throw $e;

        } catch (\Exception $e) {
            $this->sendError('Nepodařilo se nouzově prodloužit rezervaci.', Response::S400_BAD_REQUEST);
            Debugger::log($e);
        }

        $results = [
            'extended_id' => $reservation_id,
            'price' => $extendedReservation->price,
            'origin_price' => $extendedReservation->originPrice,
            'from' => $extendedReservation->from->format('c'),
            'to' => $extendedReservation->to->format('c'),
            'state' => $extendedReservation->state,
            'place_identifier' => $extendedReservation->place->identifier,
            'place_name' => $extendedReservation->place->name,
            'place_street_name' => $extendedReservation->place->streetName,
        ];

        $this->sendApiResponse($results);
    }

    public function actionCreate($data)
    {
        try {
            $d = json_decode($data);
            if (!$d) {
                $this->sendError('Request body is not in our JSON format. Read the manual or call me, baby!', Response::S400_BAD_REQUEST);
            }

            if (!$d->reservation_id) {
                $this->sendError('Reservation_id is not set. ', Response::S400_BAD_REQUEST);
            }

            if (!$d->extra) {
                $this->sendError('Extra is not set.', Response::S400_BAD_REQUEST);
            }

            /** @var Reservation $reservation */
            $reservation = $this->model->reservations->getById($d->reservation_id);
            if (!$reservation) {
                $this->sendError('Reservation not found ', Response::S400_BAD_REQUEST);
            }

            try {
                /** @var Reservation $extendedReservation */
                $extendedReservation = $this->extendReservationFacade->createExtendReservation($reservation, $d->extra);
            } catch (InvalidStateException $e) {
                Debugger::log($e);
                $this->sendError('Nepodařilo se neouzove prodloužit rezervaci.', Response::S400_BAD_REQUEST, $e->getMessage());

            } catch (InvalidReservationStateException $e) {
                Debugger::log($e);
                if ($e->state === Reservation::STATE_EXTENDED) {
                    $this->sendError('Tuto rezervaci nelze nouzově prodloužít, ovlivnila by tím jinou nouzově prodlouženou rezervaci.', Response::S400_BAD_REQUEST);
                } else {
                    $this->sendError('Tuto rezervaci nelze nouzově prodloužit', Response::S400_BAD_REQUEST, $e->getMessage());
                }
            } catch (AbortException $e) {
                throw $e;

            } catch (\Exception $e) {
                $this->sendError('Nepodařilo se nouzově prodloužit rezervaci.', Response::S400_BAD_REQUEST, $e->getMessage());
                Debugger::log($e);
            }

            /** @var User $user */
            $user = $this->model->users->getById($this->user->id);
            $this->orderFacade->payOrderByCredit($extendedReservation->order, $user);

            $results = [
                'reservation_id' => $extendedReservation->id,
                'price' => $extendedReservation->price,
                'origin_price' => $extendedReservation->originPrice,
                'from' => $extendedReservation->from->format('c'),
                'to' => $extendedReservation->to->format('c'),
                'state' => $extendedReservation->state,
                'place_identifier' => $extendedReservation->place->identifier,
                'place_name' => $extendedReservation->place->name,
                'place_street_name' => $extendedReservation->place->streetName,
            ];
        } catch (AuthenticationException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }

        $this->sendResponse(new JsonResponse($results));
    }
}
