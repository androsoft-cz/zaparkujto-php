<?php

namespace App\Modules\Admin\Components\HomepageStats;

use App\Core\UI\BaseControl;
use App\Model\Orm\Orders\OrdersMapper;
use App\Model\Orm\Orders\OrdersRepository;
use App\Model\Orm\Places\PlacesMapper;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Reservations\ReservationsMapper;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\Users\UsersMapper;
use App\Model\Orm\Users\UsersRepository;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class HomepageStatsControl extends BaseControl
{

    /** @var User */
    private $user;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var UsersMapper */
    private $usersMapper;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var ReservationsMapper */
    private $reservationsMapper;

    /** @var OrdersRepository */
    private $ordersRepository;

    /** @var PlacesRepository */
    private $placesRepository;

    /** @var OrdersMapper  */
    private $ordersMapper;

    /** @var  PlacesMapper */
    private $placesMapper;

    private $myUserData;


    public function __construct(
        User $user,
        UsersRepository $usersRepository,
        UsersMapper $usersMapper,
        ReservationsRepository $reservationsRepository,
        ReservationsMapper $reservationsMapper,
        OrdersMapper $ordersMapper,
        OrdersRepository $ordersRepository,
        PlacesRepository $placesRepository,
        PlacesMapper $placesMapper
    )
    {
        $this->user = $user;
        $this->usersRepository = $usersRepository;
        $this->usersMapper = $usersMapper;
        $this->reservationsRepository = $reservationsRepository;
        $this->reservationsMapper = $reservationsMapper;
        $this->ordersMapper = $ordersMapper;
        $this->ordersRepository = $ordersRepository;
        $this->placesRepository = $placesRepository;
        $this->placesMapper = $placesMapper;
    }

    public function setUserData($userData) {
        $this->myUserData = $userData;
    }

    public function render()
    {
        /** @var \App\Model\Orm\Users\User $user */
        $user = $this->usersRepository->findById($this->user->getId())->fetch();
        if (isset($user->organization)) {
            $organizationId = $user->organization->id;
        } else {
            $organizationId = $this->myUserData['rooted_org_id'];
        }

        $dateRange = $this->getDateRange();
        $this->template->orgIncome = $this->reservationsMapper->getOrganizationIncome($organizationId);
        $this->template->orgPlaces = $this->placesRepository->mapper->getCapacityOfPlaces($organizationId);
        $this->template->orgReservations = $this->reservationsRepository->findBy([
            'this->place->organization->id' => $organizationId,
            'this->order->state' => 3
        ])->count();

        $this->template->orgUsers = $this->ordersMapper->getTotalUsers($organizationId);

        $this->template->usersData = $this->prepareUsersData($organizationId, $dateRange);
        $this->template->reservationsData = $this->prepareReservationsData($organizationId, $dateRange);
        $this->template->dateRange = $dateRange;

        $this->template->setFile(__DIR__ . '/templates/default.latte');
        $this->template->render();
    }


    /**
     * @param int
     * @param ArrayHash
     * @return array
     */
    private function prepareReservationsData($organizationId, $dateRange)
    {
        $result = [];
        $data = $this->reservationsMapper->getReservationsPerYearMonth($organizationId, $dateRange->from, $dateRange->to);

        foreach ($dateRange->items as $key) {
            $result[$key] = 0;

            if (isset($data[$key])) {
                $result[$key] = $data[$key]->total;
            }
        }

        return $result;
    }


    /**
     * @param int
     * @param ArrayHash
     * @return array
     */
    private function prepareUsersData($organizationId, $dateRange)
    {
        $result = [];
        $data = $this->ordersMapper->getUsersPerYearMonth($organizationId, $dateRange->from, $dateRange->to);
        //$data = $this->usersMapper->getUsersPerYearMonth($organizationId, $dateRange->from, $dateRange->to);
        $usersTotal = $this->usersMapper->getTotalUsers($organizationId, NULL, $dateRange->from->modifyClone('- 1 day'));

        foreach ($dateRange->items as $key) {
            $result[$key] = $usersTotal;

            if (isset($data[$key])) {
                $usersTotal = $data[$key]->total;
                $result[$key] = $usersTotal;
            }
        }

        return $result;
    }


    /**
     * @return ArrayHash
     */
    private function getDateRange()
    {
        $now = new DateTime(date('Y-m-01'));
        $from = $now->modifyClone('- 11 months');
        $to = $now->modifyClone('+ 1 months')->modify('- 1 day');
        $items = [];

        for ($i = 0; $i <= 11; $i++) {
            $items[] = $from->modifyClone("+ $i months")->format('Y-m');
        }

        return ArrayHash::from([
            'from' => $from,
            'to' => $to,
            'items' => $items,
        ]);
    }

}
