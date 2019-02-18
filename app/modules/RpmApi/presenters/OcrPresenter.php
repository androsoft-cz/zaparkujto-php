<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Users\User;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;
use Nette\Utils\DateTime;

class OcrPresenter extends SecurePresenter
{

    public function actionReadAll(array $query)
    {
        $results = [];
        $this->sendApiResponse($results);
    }

    public function actionCreate($data)
    {
        $now = new DateTime();
        $results = [];

        $results[] = [
                'plate_number' => '4T11111',
                'paid_to' => $now->format('c')
            ];

        $results[] = [
            'plate_number' => '8T91111',
            'paid_to' => NULL
        ];

        $this->sendResponse(new JsonResponse($results));
    }
}
