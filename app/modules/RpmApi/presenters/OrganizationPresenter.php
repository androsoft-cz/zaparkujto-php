<?php

namespace App\Modules\Rpmapi;

use App\Core\Config\Config;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\TimeLogic\ITimeAnalyzatorFactory;
use Nette\Http\Response;

class OrganizationPresenter extends SecurePresenter
{

    /** @var OrganizationsRepository @inject */
    public $organizationRepository;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var ITimeAnalyzatorFactory @inject */
    public $timeAnalyzatorFactory;

    /** @var Config @inject */
    public $config;


    public function actionRead($id, array $query)
    {
        if ($id == NULL) {
            $this->sendError('Request body is not in our JSON format. ID is not set', Response::S400_BAD_REQUEST);
        }

        try {
            $organization = $this->organizationRepository->getById($id);

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
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'address1' => $organization->contact->address1,
                'address2' => $organization->contact->address2,
                'telephone' => $organization->contact->telephone,
                'email' => $organization->contact->email,
                'company' => $organization->contact->company,
                'identification_number' => $organization->contact->identificationNumber,
                'tax_identification_number' => $organization->contact->taxIdentificationNumber,
                'lng' => $organization->lng,
                'lat' => $organization->lat,
                'time_diff_notification' => $this->config->expand('diff.notification'),
                'time_diff_minimal' => $this->config->expand('diff.minimal'),
                'time_logic_show_min_reservation_start' => $this->config->expand('logic.show_min_reservation_start'),
                'time_logic_show_max_reservation_end' => $this->config->expand('logic.show_max_reservation_end'),
                'time_logic_time_park_long_time' => $this->config->expand('logic.time_park_long_time'),
                'time_logic_tolerance_time' => $this->config->expand('logic.tolerance_time'),
                'time_reservation_release_canceled_before' => $this->config->expand('reservation.release.canceled_before'),
                'time_reservation_release_canceled_during' => $this->config->expand('reservation.release.canceled_during'),
                'time_reservation_extend_min_before' => $this->config->expand('reservation.extend.extend_min_before'),
                'time_reservation_extend_blocking_time' => $this->config->expand('reservation.extend.extend_blocking_time'),
                'time_reservation_extend_min_time' => $this->config->expand('reservation.extend.extend_min_time'),
                'time_reservation_extend_max_time' => $this->config->expand('reservation.extend.extend_max_time'),
                'your_place_id' => $your_place ? $your_place->id : NULL,
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
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
            /** @var Organization $organization */
            $organization = $this->organizationRequest->getOrganization();
            if (!$organization) {
                $this->sendError('Uživatel nemá přiřazenou organizaci', Response::S400_BAD_REQUEST);
            }

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
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'address1' => $organization->contact->address1,
                'address2' => $organization->contact->address2,
                'telephone' => $organization->contact->telephone,
                'email' => $organization->contact->email,
                'company' => $organization->contact->company,
                'identification_number' => $organization->contact->identificationNumber,
                'tax_identification_number' => $organization->contact->taxIdentificationNumber,
                'lng' => $organization->lng,
                'lat' => $organization->lat,
                'time_diff_notification' => $this->config->expand('diff.notification'),
                'time_diff_minimal' => $this->config->expand('diff.minimal'),
                'time_logic_show_min_reservation_start' => $this->config->expand('logic.show_min_reservation_start'),
                'time_logic_show_max_reservation_end' => $this->config->expand('logic.show_max_reservation_end'),
                'time_logic_time_park_long_time' => $this->config->expand('logic.time_park_long_time'),
                'time_logic_tolerance_time' => $this->config->expand('logic.tolerance_time'),
                'time_reservation_release_canceled_before' => $this->config->expand('reservation.release.canceled_before'),
                'time_reservation_release_canceled_during' => $this->config->expand('reservation.release.canceled_during'),
                'time_reservation_extend_min_before' => $this->config->expand('reservation.extend.extend_min_before'),
                'time_reservation_extend_blocking_time' => $this->config->expand('reservation.extend.extend_blocking_time'),
                'time_reservation_extend_min_time' => $this->config->expand('reservation.extend.extend_min_time'),
                'time_reservation_extend_max_time' => $this->config->expand('reservation.extend.extend_max_time'),
                'your_place_id' => $your_place ? $your_place->id : NULL,
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }

    }
}
