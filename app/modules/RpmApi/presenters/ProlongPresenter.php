<?php

namespace App\Modules\Rpmapi;

use App\Core\Config\Config;
use App\Model\Exceptions\RuntimeException;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use Exception;
use Nette\Http\Response;

class ProlongPresenter extends SecurePresenter
{
    /** @var ReservationsRepository  @inject */
    public $reservationsRepository;

    /** @var PlaceReservator @inject */
    public $placeReservator;

    /** @var  Config @inject */
    public $config;


    public function actionCreate($data)
    {
        $results = [];
        $d = json_decode($data);

        if (!$d || !isset($d->reservation_id, $d->parking_time, $d->type)) {
            $this->sendError('invalidJsonFormat', Response::S400_BAD_REQUEST);
        }

        /** @var Reservation $reservation */
        $reservation = $this->reservationsRepository->getById($d->reservation_id);

        if (!$reservation) {
            $exception = (new RuntimeException('Reservation not found.'))->setApiMessage('itemNotFound');
            $this->sendErrorByException($exception);
        }

        try {
            $isCheck = $d->type == 'check';

            $context = new PlaceReservatorContext();
            $context->placeId = $reservation->place->id;
            $context->parkingTime = $d->parking_time;
            $context->user = $reservation->user;
            $context->rz = $reservation->rz;
            $context->reserveFrom = $reservation->to->getTimestamp();
            $context->presenter = $this;
            $context->usePaymentCard = $d->paymentCard ?? FALSE;
            $context->isCheck = $isCheck;

            if ($context->reserveFrom < time()) {
                $context->reserveFrom = time();
            }

            $notification = $this->config->expand('diff.notification');
            if ($isCheck) {
                $reservatorResult = $this->placeReservator->check($context);
                $results = [
                    'result' => 'check',
                    'price' => $reservatorResult->price,
                    'from_date' => $reservatorResult->from,
                    'to_date' => $reservatorResult->to,
                    'time_diff_notification' => $notification,
                ];
            } else {
                $reservatorResult = $this->placeReservator->reserve($context);
                $results = [
                    'result' => 'reserved',
                    'reservation_id' => $reservatorResult->reservation->id,
                    'organization_id' => $reservatorResult->reservation->place->organization->id,
                    'order_id' => $reservatorResult->order->id,
                    'gateway_result' => $reservatorResult->gatewayResult,
                    'price' => $reservatorResult->reservation->price,
                    'from_date' => $reservatorResult->reservation->from->format('c'),
                    'to_date' => $reservatorResult->reservation->to->format('c'),
                    'time_diff_notification' => $notification,
                ];
            }
        } catch (Exception $e) {
            $this->sendErrorByException($e, 'orderProcessingError');
        }

        $this->sendApiResponse($results);
    }
}
