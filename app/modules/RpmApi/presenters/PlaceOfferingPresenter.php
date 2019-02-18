<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Nette\Http\Response;

class PlaceOfferingPresenter extends SecurePresenter
{

    /** @var UsersRepository @inject */
    public $usersRepository;


    /**
     * Offer or disable offering of place
     * @param mixed $data
     */
    public function actionCreate($data)
    {
        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (!isset($d->place_offered)) {
            $this->sendError('Parameter place_offered is not set. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        try {
            /** @var User $card */
            $card = $this->usersRepository->getById($this->user->id);
            $card->shareable = $d->place_offered ? 1 : 0;

            $this->usersRepository->persistAndFlush($card);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);
        } catch (\ErrorException $e) {
            $this->sendError('Error while saving data!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

}
