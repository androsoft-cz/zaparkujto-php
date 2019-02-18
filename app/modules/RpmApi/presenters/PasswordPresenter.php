<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Search\SearchArgs;
use Nette\Http\Response;
use Nette\Security\Passwords;

class PasswordPresenter extends SecurePresenter
{

    /** @var SearchArgs @inject */
    public $search;

    /** @var UsersRepository @inject */
    public $usersRepository;


    /**
     * @param array $query
     * @param mixed $data
     */
    public function actionCreate($data)
    {

        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (!isset($d->type) && !isset($d->oldpassword) && !isset($d->newpassword)) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        try {

            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);
            if (!Passwords::verify($d->oldpassword, $user->password)) {
                $this->sendError('Old password is incorrect!', Response::S409_CONFLICT);
            }

            $user->setPassword($d->newpassword);
            $this->usersRepository->persistAndFlush($user);

            $results = [
                'code' => 200,
                'result' => 'changed',
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError('Error while changing password!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
