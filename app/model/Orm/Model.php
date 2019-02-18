<?php

namespace App\Model\Orm;

use App\Model\Orm\ApiTokens\ApiTokensRepository;
use App\Model\Orm\Contacts\ContactsRepository;
use App\Model\Orm\PaymentCards\PaymentCardsRepository;
use App\Model\Orm\Credits\CreditsRepository;
use App\Model\Orm\Inspections\InspectionsRepository;
use App\Model\Orm\Maintenances\MaintenancesRepository;
use App\Model\Orm\Orders\OrdersRepository;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\PaymentGateways\PaymentGatewaysRepository;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Prescriptions\PrescriptionsRepository;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\ReservationTokens\ReservationTokensRepository;
use App\Model\Orm\SenzorLogs\SenzorLogsRepository;
use App\Model\Orm\TimeWindows\TimeWindowsRepository;
use App\Model\Orm\UseofPlaces\UseofPlacesRepository;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\UsersRz\UsersRzRepository;
use Nextras\Orm\Model\Model as Orm;

/**
 * @property-read OrganizationsRepository     $organizations
 * @property-read UsersRepository             $users
 * @property-read ContactsRepository          $contacts
 * @property-read InspectionsRepository       $inspections
 * @property-read MaintenancesRepository      $maintenances
 * @property-read PlacesRepository            $places
 * @property-read UseofPlacesRepository       $useofPlaces
 * @property-read ReservationsRepository      $reservations
 * @property-read ReservationTokensRepository $reservationTokens
 * @property-read OrdersRepository            $orders
 * @property-read ApiTokensRepository         $apiTokens
 * @property-read CreditsRepository           $credits
 * @property-read TimeWindowsRepository       $timeWindows
 * @property-read SenzorLogsRepository        $senzorLogs
 * @property-read PositionsRepository         $positions
 * @property-read UsersRzRepository           $usersRz
 * @property-read PaymentCardsRepository      $paymentCards
 * @property-read PaymentGatewaysRepository   $paymentGateways
 * @property-read PrescriptionsRepository     $prescriptions
 */
class Model extends Orm
{
}
