<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UserRzHelper;
use App\Model\Security\Authorizator;
use Nette\Http\Response;
use Nette\Utils\DateTime;

class RegistrationPresenter extends BasePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var UserRzHelper @inject */
    public $userRzHelper;


    /**
     * @param array $query
     * @param mixed $data
     */
    public function actionCreate($data)
    {
        $user = FALSE;
        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (isset($d->username)) {
            $user = $this->usersRepository->getBy(['username' => $d->username]);
        } else {
            $this->sendError('Uživatelské jméno není vyplněno!', Response::S400_BAD_REQUEST);
        }

        if ($user) {
            $this->sendError('Toto uživatelské jméno už existuje!', Response::S400_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = new User();
            $user->contact = new Contact();
            $user->setPassword($d->password);
            $user->role = Authorizator::USER;
            $user->createdAt = new DateTime();

            $user->username = $d->username;
            if (!isset($d->email)) {
                $user->contact->email = $d->username;
            } else {
                $user->contact->email = $d->email;
            }

            if (isset($d->telephone)) $user->contact->telephone = $d->telephone;
            if (isset($d->address1)) $user->contact->address1 = $d->address1;
            if (isset($d->address2)) $user->contact->address2 = $d->address2;
            if (isset($d->identification_number)) $user->contact->identificationNumber = $d->identification_number;
            if (isset($d->tax_identification_number)) $user->contact->taxIdentificationNumber = $d->tax_identification_number;
            $user->contact->state = '';

            if (isset($d->name)) $user->name = $d->name;

            $this->usersRepository->persistAndFlush($user);

            $this->userRzHelper->updateRzs($user, (array) $d->rz);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError('Chyba při registraci.!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

}
