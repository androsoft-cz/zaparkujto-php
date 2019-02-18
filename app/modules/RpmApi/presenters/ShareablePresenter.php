<?php

namespace App\Modules\Rpmapi;

use App\Model\Facade\CardFacade;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Nette\Http\Response;

class ShareablePresenter extends SecurePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var CardFacade @inject */
    public $cardFacade;


    /**
     * @param $id
     */
    public function actionRead($id)
    {
        if ($id != $this->user->id) {
            $this->sendError('Access denied', Response::S401_UNAUTHORIZED);
        }

        /** @var User $card */
        $card = $this->usersRepository->getById($id);

        $results = [
            'shareable_enabled' => $card->isShareableEnabled(),
            'manual_shareable' => (bool) $card->manualShareable,
            'shareable' => (bool) $card->shareable,
            'currently_shareable' => $this->cardFacade->isCurrentlyShareable($card),
        ];

        $this->sendApiResponse($results);
    }


    /**
     * Offer or disable offering of place
     * @param mixed $data
     */
    public function actionUpdate($id, $data)
    {
        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (!isset($d->shareable) && !isset($d->manual_shareable)) {
            $this->sendError('Parameter "shareable" of "manual_shareable" must be set. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if ($id != $this->user->id) {
            $this->sendError('Access denied', Response::S401_UNAUTHORIZED);
        }

        try {
            /** @var User $card */
            $card = $this->usersRepository->getById($id);

            if (isset($d->shareable)) {
                $card->shareable = $d->shareable ? 1 : 0;
            }

            if (isset($d->manual_shareable)) {
                $card->manualShareable = $d->manual_shareable ? 1 : 0;
            }

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
