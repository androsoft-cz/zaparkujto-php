<?php

namespace App\Modules\Rpmapi;

use App\Core\Config\Config;
use App\Model\Facade\OrderFacade;
use App\Model\Facade\ReserveFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;
use Nextras\Orm\Collection\ICollection;
use Exception;

class ReservationsPresenter extends SecurePresenter
{
    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var ReservationsRepository @inject */
    public $reservationsRepository;

    /** @var UsersRepository @inject */
    public $userRepository;

    /** @var ReserveFacade @inject */
    public $reserveFacade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var PlaceReservator @inject */
    public $placeReservator;

    /** @var  Config @inject */
    public $config;


    public function actionRead($id, array $query, array $associations)
    {
        if ($associations) {
            $this->reservationsRepository->findBy(['this->places->id' => $associations->places]);
            dump('all' . json_encode($associations));
            die();
        }

        /** @var Reservation $r */
        $r = $this->reservationsRepository->findById($id)->fetch();
        if (!$r) {
            return $this->sendError('Rezervace s tímto ID neexistuje', 403);
        }

        if (isset($query['action'])) {
            if ($query['action'] == 'cancel') {
                if ($r->order->state == Order::STATE_STORNO) {
                    $result = [];
                    $this->sendError('Rezervace je už stornována', 403);
                } else {
                    $this->orderFacade->cancelOrderByReservation($id);
                    $result = [
                        'reservation_id' => $id,
                    ];
                }
            }
        } else {
            if ($r->order->state == Order::STATE_WAITING) {
                $this->orderFacade->verifyOrderPayment($r->order->id);
                $r = $this->reservationsRepository->findById($id)->fetch();
            }

            $result = [
                'reservation_id' => $r->id,
                'organization_id' => $r->place->organization->id,
                'place_id' => $r->place->id,
                'street_name' => $r->place->streetName,
                'identifier' => $r->place->identifier,
                'lng' => $r->place->lng,
                'lat' => $r->place->lat,
                'rz' => $r->rz,
                'price' => $r->price,
                'from_date' => $r->from->format('c'),
                'to_date' => $r->to->format('c'),
                'place_type' => $r->useofPlaces->type,
                'gateway_result' => array('gw_url' => $r->order->gatewayUrl)
            ];

            if (isset($r->order)) {
                $result += [
                    'paid_at' => ($r->order->paidAt) ? $r->order->paidAt->format('c') : NULL,
                    'order_state' => $r->order->state,
                ];

                if (isset($r->order->paymentType)) {
                    $result += ['payment_type' => $r->order->paymentType];
                }
            }
        }

        $this->sendResponse(new JsonResponse($result));
    }


    public function actionReadAll(array $query, array $associations)
    {
        if ($associations) { // je jedno jestli pouzijeme place nebo places
            if (isset($associations['place'])) {
                $placeId = $associations['place'];
            } elseif (isset($associations['places'])) {
                $placeId = $associations['places'];
            } else {
                $this->sendError('Vazba není správně definována', 500);
            }

            $place = $this->placesRepository->getById($placeId);
            if (isset($query['time'])) {
                if ($query['time'] == 'last') {
                    $reservations = $this->reservationsRepository->findLastByPlace($placeId);
                } elseif ($query['time'] == 'future') {
                    $reservations = $this->reservationsRepository->findFutureByPlace($placeId);
                } elseif ($query['time'] == 'present') {
                    $reservations = $this->reservationsRepository->findPresentByPlace($placeId);
                    $sameSmsZonePresentReservations = $this->reservationsRepository->findPresentBySmsZone($place->smsKeyword);
                } elseif ($query['time'] == 'waiting') {
                    $reservations = $this->reservationsRepository->findWaitingByPlace($placeId);
                } elseif ($query['time'] == 'group') {
                    $reservationsLast = $this->reservationsRepository->findLastByPlace($placeId)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    //$reservationsFuture = $this->reservationsRepository->findFutureByPlace($placeId)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    $reservationsPresent = $this->reservationsRepository->findPresentByPlace($placeId)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    $sameSmsZonePresentReservations = $this->reservationsRepository->findPresentBySmsZone($place->smsKeyword);
                    $reservationsWaiting = $this->reservationsRepository->findWaitingByPlace($placeId)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);

                    $results = [
                        'waiting' => $this->toArrayResult($reservationsWaiting),
                        'present' => array_merge($this->toArrayResult($reservationsPresent), $this->toArrayResult($sameSmsZonePresentReservations)),
                        'last' => $this->toArrayResult($reservationsLast),
                        'future' => [],

                    ];

                } else {
                    $reservations = $this->reservationsRepository->findPresentByPlace($placeId);
                    $sameSmsZonePresentReservations = $this->reservationsRepository->findPresentBySmsZone($place->smsKeyword);
                }
            }

            if (!isset($reservations)) {
                $reservations = $this->reservationsRepository->findPresentByPlace($placeId);
                $sameSmsZonePresentReservations = $this->reservationsRepository->findPresentBySmsZone($place->smsKeyword);
            }
        } else {
            if ((!isset($reservations)) && (isset($query['rz'])) && (!isset($results))) {
                $rz = $query['rz'];
                if (isset($query['time'])) {
                    if ($query['time'] == 'last') {
                        $reservations = $this->reservationsRepository->findLastByRz($rz);
                    } elseif ($query['time'] == 'future') {
                        $reservations = $this->reservationsRepository->findFutureByRz($rz);
                    } elseif ($query['time'] == 'present') {
                        $reservations = $this->reservationsRepository->findPresentByRz($rz);
                    } elseif ($query['time'] == 'waiting') {
                        $reservations = $this->reservationsRepository->findWaitingByRz($rz);
                    } elseif ($query['time'] == 'canceled') {
                        $reservations = $this->reservationsRepository->findCanceledByRz($rz);
                    } elseif ($query['time'] == 'group') {
                        $reservationsLast = $this->reservationsRepository->findLastByRz($rz)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                        //$reservationsFuture = $this->reservationsRepository->findFutureByRz($rz)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                        $reservationsPresent = $this->reservationsRepository->findPresentByRz($rz)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                        $reservationsWaiting = $this->reservationsRepository->findWaitingByRz($rz)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);

                        $results = [
                            'waiting' => $this->toArrayResult($reservationsWaiting),
                            'present' => $this->toArrayResult($reservationsPresent),
                            'last' => $this->toArrayResult($reservationsLast),
                            'future' => [],
                        ];
                    } else {
                        $reservations = $this->reservationsRepository->findPresentByRz($rz);
                    }
                } else {
                    $reservations = $this->reservationsRepository->findPresentByRz($rz);
                }
            }

            if ((!isset($reservations)) && (isset($query['time'])) && (!isset($results))) {
                if ($query['time'] == 'last') {
                    $reservations = $this->reservationsRepository->findLast($this->user->id);
                } elseif ($query['time'] == 'future') {
                    $reservations = $this->reservationsRepository->findFuture($this->user->id);
                } elseif ($query['time'] == 'present') {
                    $reservations = $this->reservationsRepository->findPresent($this->user->id);
                } elseif ($query['time'] == 'waiting') {
                    $reservations = $this->reservationsRepository->findWaiting($this->user->id);
                } elseif ($query['time'] == 'group') {
                    $reservationsLast = $this->reservationsRepository->findLast($this->user->id)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    //$reservationsFuture = $this->reservationsRepository->findFuture($this->user->id)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    $reservationsPresent = $this->reservationsRepository->findPresent($this->user->id)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
                    $reservationsWaiting = $this->reservationsRepository->findWaiting($this->user->id)->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);

                    $results = [
                        'waiting' => $this->toArrayResult($reservationsWaiting),
                        'present' => $this->toArrayResult($reservationsPresent),
                        'last' => $this->toArrayResult($reservationsLast),
                        'future' => [],
                    ];
                } else {
                    $reservations = $this->reservationsRepository->findPresent($this->user->id);
                }
            }

            if (!isset($reservations) && (!isset($results))) {
                $reservations = $this->reservationsRepository->findPresent($this->user->id);
            }
        }

        if (!isset($results)) {
            $reservations = $reservations->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
            $results = [];
            $notification = $this->config->expand('diff.notification');
            $results[] = $this->toArrayResult($reservations);
            if (isset($sameSmsZonePresentReservations)) {
                $results[0][] = $this->toArrayResult($sameSmsZonePresentReservations)[0];
            }
        }

        $this->sendResponse(new JsonResponse($results));
    }


    public function toArrayResult($reservations)
    {
        $reservations = $reservations->orderBy('createdAt', ICollection::DESC)->orderBy('from', ICollection::DESC)->limitBy(50);
        $results = [];
        $notification = $this->config->expand('diff.notification');

        /** @var Reservation $r */
        foreach ($reservations as $r) {
            $results[] = [
                'reservation_id' => $r->id,
                'place_id' => $r->place->id,
                'organization_id' => $r->place->organization->id,
                'street_name' => $r->place->streetName,
                'identifier' => $r->place->identifier,
                'lng' => $r->place->lng,
                'lat' => $r->place->lat,
                'rz' => $r->rz,
                'price' => $r->price,
                'from_date' => ($r->from) ? $r->from->format('c') : NULL,
                'to_date' => ($r->to) ? $r->to->format('c') : NULL,
                'payment_type' => $r->order->paymentType,
                'time_diff_notification' => $notification,
                'paid_at' => ($r->order->paidAt) ? $r->order->paidAt->format('c') : NULL,
                'order_state' => $r->order->state,
                'place_type' => $r->useofPlaces->type,
            ];
        }

        return $results;
    }


    public function actionCreate(string $data): void
    {
        $notification = $this->config->expand('diff.notification');
        $results = [];
        $data = json_decode($data);

        if (!$data || !isset($data->place_id, $data->parking_time, $data->type)) {
            $this->sendError('invalidJsonFormat', Response::S400_BAD_REQUEST);
        }

        $isCheck = $data->type === 'check';
        if (!$isCheck && !isset($data->rz)) {
            $this->sendError('invalidJsonFormat, rz is missing', Response::S400_BAD_REQUEST);
        }

        try {
            $context = new PlaceReservatorContext();
            $context->placeId = $data->place_id;
            $context->parkingTime = $data->parking_time;
            $context->user = $this->userRepository->getById($this->user->id);
            $context->rz = $data->rz ?? NULL;
            $context->usePaymentCard = $data->paymentCard ?? FALSE;
            $context->presenter = $this;
            $context->isCheck = $isCheck;

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
            $this->sendErrorByException($e, $e->getMessage());
        }

        $this->sendApiResponse($results);
    }
}
