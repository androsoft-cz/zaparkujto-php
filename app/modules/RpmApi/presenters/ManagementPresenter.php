<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UsersRzMapper;
use App\Model\Search\SearchArgs;
use Nette\Http\Response;

class ManagementPresenter extends SecurePresenter
{
    /** @var SearchArgs @inject */
    public $search;

    /** @var UsersRepository @inject */
    public $userRepository;

    /** @var OrganizationsRepository @inject */
    public $organizationRepository;

    /** @var UsersRzMapper @inject */
    public $usersRzMapper;


    /**
     * Přidělení kreditu všem residentním uživatelům organizace
     */
    public function actionReadAll()
    {
        try {

            /** @var User $user */
            $user = $this->userRepository->getById($this->user->id);
            $rsz = $this->usersRzMapper->findAllUserRzs($user);

            $results = [
                'id' => $user->id,
                'login' => $user->username,
                'name' => $user->name,
                'address1' => $user->contact->address1,
                'address2' => $user->contact->address2,
                'telephone' => $user->contact->telephone,
                'email' => $user->contact->email,
                'company' => $user->contact->company,
                'identification_number' => $user->contact->identificationNumber,
                'tax_identification_number' => $user->contact->taxIdentificationNumber,
                'rz' => $rsz,
                'creditAmount' => $user->creditAmount,
                'type' => $user->type,
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }

    }
}
