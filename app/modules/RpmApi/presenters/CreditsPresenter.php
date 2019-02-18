<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Credits\CreditsRepository;
use App\Model\Orm\Users\UsersRepository;
use Nette\Http\Response;

class CreditsPresenter extends SecurePresenter
{

    /** @var CreditsRepository @inject */
    public $creditsRepository;

    /** @var UsersRepository @inject */
    public $userRepository;

    /**
     * List of places
     */
    public function actionReadAll()
    {

        $userId = $this->user->id;

        try {
            /** @var Credit $credits */
            $credits = $this->creditsRepository->findBy([
                'this->user->id' => $userId,
                'deletedAt' => NULL,
            ])->limitBy(100);

            $results = [];

            /** @var Credit $c */
            foreach ($credits as $c) {
                $r1 = [
                    'id' => $c->id,
                    'price' => $c->price,
                    'created_at' => $c->createdAt->format('c'),
                    'movement_type' => $c->movementType,
                ];

                if ($c->order) {
                    $reservations = $c->order->reservations;
                    $r = $reservations->get()->fetch();

                    $r2 = [
                        //$r1['place_identifier'] = $r->place->identifier,
                        'place_identifier' => $r->place->identifier,
                        'street_name' => $r->place->streetName,
                        'reserved_from' => $r->from->format('c'),
                        'reserved_to' => $r->to->format('c'),
                    ];
                } else {
                    $r2 = [];
                }

                $results[] = array_merge($r1, $r2);
            }

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
