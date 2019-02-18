<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use App\Model\Reservations\PlaceReservatorResult;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class Rcev1Presenter extends BasePresenter
{

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var PlaceReservator @inject */
    public $placeReservator;

    public function startup()
    {
        parent::startup();
        $this->authenticateByBasic($this->query);
    }

    public function actionCreate($data)
    {
        Debugger::log($data, Debugger::DEBUG);

        try {
            $json = \Nette\Utils\Json::decode($data);
        } catch (\Exception $e) {
            $json = FALSE;
        }

        $structure = TRUE;
        if (!isset($json->parking_zone_id)) $structure = FALSE;
        if (!isset($json->free_lots)) $structure = FALSE;
        if (!isset($json->checked_lots)) $structure = FALSE;

        if (!$structure || !$json) {
            $this->sendError('Bad body structure', Response::S400_BAD_REQUEST);
        }

        $parkingZoneId = $json->parking_zone_id;
        $freeLots = $json->free_lots;
        $checkedLots = $json->checked_lots;

        /** @var Place $place */
        $place = $this->placesRepository->findByWithoutOrganization([
            'id' => $parkingZoneId
        ])->fetch();

        if (!$place) {
            $this->sendError('Parking zone ID not found', Response::S404_NOT_FOUND);
        }

        $result = ['result' => 'ok', 'message' => 'Information accepted'];
        $this->sendApiResponse($result);
    }

    public function authenticateByBasic(array $query)
    {
        $authorization = explode(' ', $this->getHttpRequest()->getHeader('Authorization'));
        if ($authorization[0] == 'Basic') {
            $access_token = $authorization[1];
        }

        if (!isset($access_token)) {
            if (isset($query['access_token'])) {
                $access_token = $query['access_token'];
            }
        }

        if (!isset($access_token)) {
            $this->sendError('Access token is not set', Response::S403_FORBIDDEN);
        }

        if ($access_token !== 'LfRjjOBpjNeQILG') {
            $this->sendError('Wrong access token', Response::S403_FORBIDDEN);
        }

    }

}
