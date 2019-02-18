<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use App\Model\Reservations\PlaceReservatorResult;
use App\Model\Reservations\SmsReservator;
use App\Model\Search\SearchArgs;
use App\Model\Search\UseofPlaceSelector;
use Nette\Http\Response;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class GlobdataPresenter extends BasePresenter
{

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var SmsReservator @inject */
    public $smsReservator;

    /** @var UseofPlaceSelector @inject */
    public $useOfPlaceSelector;

    public function actionCreate($data)
    {
        $now = new DateTime();
        Debugger::log($data, Debugger::DEBUG);

        try {
            $json = \Nette\Utils\Json::decode($data);
        } catch (\Nette\Utils\JsonException $e) {
            $json = FALSE;
        }

        try {
            if ($json) {
                $id = isset($json->id) ? $json->id : '';

                if (isset($json->city)) {
                    $city = $json->city;
                } else {
                    $this->sendError('city is missing', Response::S400_BAD_REQUEST);
                }

                if (isset($json->zone)) {
                    $zone = $json->zone;
                } else {
                    $this->sendError('zone is missing', Response::S400_BAD_REQUEST);
                }

                if (isset($json->paid_from)) {
                    try {
                        $temp = str_replace(['Z', 'T'],['', ' '], $json->paid_from);
                        //$temp = $json->paid_from;
                        $paidFrom = new DateTime($temp);
                    } catch (\ErrorException $e) {
                        $this->sendError('parsing paid_from error. '.$e->getMessage(), Response::S400_BAD_REQUEST);
                    }
                } else {
                    $this->sendError('paid_from is missing', Response::S400_BAD_REQUEST);
                }

                if (isset($json->paid_to)) {
                    try {
                        $temp = str_replace(['Z', 'T'],['', ' '], $json->paid_to);
                        //$temp = $json->paid_to;
                        $paidTo = new DateTime($temp);
                    } catch (\ErrorException $e) {
                        $this->sendError('parsing paid_to error. '.$e->getMessage(), Response::S400_BAD_REQUEST);
                    }
                } else {
                    $this->sendError('paid_to is missing', Response::S400_BAD_REQUEST);
                }

                //docasne reseni nez globdata vyresi jejich problem
                $now = time();
                $paidFrom = DateTime::from($now);
                $paidTo = DateTime::from($now);

                if (isset($json->price)) {
                    $price = $json->price;
                } else {
                    $this->sendError('price is missing', Response::S400_BAD_REQUEST);
                }

                if (isset($json->licence_plate)) {
                    $licencePlate = $json->licence_plate;
                } else {
                    if (isset($json->license_plate)) {
                        $licencePlate = $json->license_plate;
                    } else {
                        $this->sendError('Licence plate is missing', Response::S400_BAD_REQUEST);
                    }
                }

                //pokud Globdata nedodá datum do, ale jenom datum od a cenu, pak si můžeme vypočítat datum do sami
                if ($paidFrom == $paidTo && $price && (int) $price > 0) {
                    $parkingtime = $this->getValmezSmsTime($zone, $price);
                    $paidTo = $paidTo->add(new \DateInterval("PT{$parkingtime}M"));
                }

                /** @var Place $place */
                $place = $this->placesRepository->findByWithoutOrganization([
                    'smsKeyword' => $zone,
                ])->fetch();

                if (!$place) {
                    $text = 'Zadané místo nebylo nalezeno';
                } else {
                    try {
                        /** @var PlaceReservatorResult $result */
                        $result = $this->smsReservator->reserve($place, $paidFrom, $parkingtime, $licencePlate, $price);

                        $text = sprintf(
                            'SMS parkovací lístek. %s, %s, Platnost od: %s do %s.',
                            $place->organization->name,
                            $place->streetName,
                            $result->reservation->from->format('d.m.Y H:i'),
                            $result->reservation->to->format('d.m.Y H:i')
                        );
                    } catch (IApiMessageException $e) {
                        Debugger::log($e, Debugger::DEBUG);
                        $text = 'Zaplacení parkovacího místa selhalo, prosím kontaktujte správce.';
                    }
                }

                $result = ['result' => 'ok', 'text' => $text];
            } else {
                $errorMessage = 'invalid json';
                $this->sendError($errorMessage, Response::S400_BAD_REQUEST);
            }

            $this->sendApiResponse($result);
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

    public function getValmezSmsTime($zone, $price)
    {
        $zone = strtoupper($zone);
        $time = NULL;

        if (($zone == 'O') || ($zone == 'B')) { //oranzova zona a bila
            switch ($price) {
                case 20:
                    $time = 60;
                    break;
                case 40:
                    $time = 120;
                    break;

            }
        }

        if (($zone == 'Z') || ($zone == 'L')) { //zelena a zluta
            switch ($price) {
                case 13:
                    $time = 75;
                    break;
                case 20:
                    $time = 120;
                    break;
            }
        }

        if (($time == NULL) && ($price > 0)) {
            $time = 60; //pokud je cena jina tak vytvorime listek alespon na hodinku aby to policie mohla resit
        }

        return $time;
    }

    public function getTimeFromPrice($paidFrom, $place, $price)
    {
        $paidTo = $paidFrom;
        $searchArgs = new SearchArgs();
        $searchArgs->setReserveFrom($paidFrom->getTimestamp());
        $useOfPlace = $this->useOfPlaceSelector->getUseofPlace($place, $searchArgs);

        if (!$useOfPlace) {
            $this->sendError('Invalid parking interval and can`t restore it.', Response::S400_BAD_REQUEST);
        }

        $pricePerMinute = $useOfPlace->pricePerUnit / 60;
        $minutes = (int) $price / $pricePerMinute;
        $paidTo = $paidTo->add(new \DateInterval("PT{$minutes}M"));

    }
}
