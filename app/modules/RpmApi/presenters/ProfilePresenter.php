<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\UsersRz\UserRzHelper;
use App\Model\Orm\UsersRz\UsersRzMapper;
use App\Model\Search\PlaceFinder;
use App\Model\Search\SearchArgs;
use App\Model\TimeLogic\ITimeAnalyzatorFactory;
use Nette\Http\Response;

class ProfilePresenter extends SecurePresenter
{
    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var OrganizationsRepository @inject */
    public $organizationRepository;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var PlaceFinder @inject */
    public $placeFinder;

    /** @var ITimeAnalyzatorFactory @inject */
    public $timeAnalyzatorFactory;

    /** @var UsersRzMapper @inject */
    public $usersRzMapper;

    /** @var UserRzHelper @inject */
    public $userRzHelper;


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

        try {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);
            $user->contact->email = $d->email;
            $user->contact->telephone = $d->telephone;
            $user->contact->address1 = $d->address1;
            $user->contact->address2 = $d->address2;
            $user->contact->identificationNumber = $d->identification_number;
            $user->contact->taxIdentificationNumber = $d->tax_identification_number;
            $user->name = $d->name;

            $this->usersRepository->persistAndFlush($user);

            $this->userRzHelper->updateRzs($user, (array) $d->rz);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError('Error while saving data!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

    public function canYouShowControlorMenu($role)
    {
        if (in_array($role, ['controlor', 'admin', 'root'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function canYouShowRedactorMenu($role)
    {
        if (in_array($role, ['redactor', 'admin', 'root'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * List of places
     *
     * @param array $query
     */
    public function actionReadAll(array $query)
    {

        try {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);
            $rsz = $this->usersRzMapper->findAllUserRzs($user);

            $my_lng = isset($query['my_lng']) ? $query['my_lng'] : NULL;
            $my_lat = isset($query['my_lat']) ? $query['my_lat'] : NULL;

            if ($my_lng && $my_lat) {
                /** @var Place $your_place */
                $your_place = $this->placesRepository->findByGps($my_lat, $my_lng)
                    ->limitBy(1)->fetch();
            } else {
                $your_place = NULL;
            }

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
                'discountForSharing' => NULL,
                'type' => $user->type,
                'city' => ($user->organization) ? $user->organization->name : NULL,
                'lng' => ($user->organization) ? $user->organization->lng : NULL,
                'lat' => ($user->organization) ? $user->organization->lat : NULL,
                'close_place_id' => $your_place ? $your_place->id : NULL, //ve významu jestli uživatel stojí poblíž nějakého parkovacího místa, nikoliv že má uživatel přiděleno parkovací místo
                'role' => $user->role,
                'show_controlor' => $this->canYouShowControlorMenu($user->role),
                'show_redactor' => $this->canYouShowRedactorMenu($user->role),
                'show_shareable_functions' => $user->isShareableEnabled(),
                'show_credit_functions' => ($user->type == User::TYPE_RESIDENT),
                'show_mojeobec_functions' => ($user->type == User::TYPE_MOJEOBEC)
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }

    }
}
