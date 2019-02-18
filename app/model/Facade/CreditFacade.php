<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Credits\CreditsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Nette\Utils\DateTime;

final class CreditFacade
{

    /** @var Config */
    private $config;

    /** @var CreditsRepository */
    private $creditsRepository;

    /** @var  UsersRepository */
    private $usersRepository;

    /**
     * @param Config $config
     * @param CreditsRepository $creditsRepository
     * @param UsersRepository $usersRepository
     */
    public function __construct(
        Config $config,
        CreditsRepository $creditsRepository,
        UsersRepository $usersRepository
    )
    {
        $this->config = $config;
        $this->creditsRepository = $creditsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function allocateCreditsToResidents($organization_id, $creditAmount)
    {

        $organizationUsers = $this->usersRepository->findBy([
            'this->organization->id' => $organization_id,
            'this->type' => User::TYPE_RESIDENT,
        ]);

        /** @var User $user */
        foreach ($organizationUsers as $user) {
            /** @var Credit $credit */
            $credit = new Credit();
            $credit->createdAt = new DateTime();
            $credit->movementType = Credit::MOVEMENT_TYPE_CHARGE_BY_CLAIM;
            $credit->price = $creditAmount;
            $credit->user = $user;

            $this->creditsRepository->persistAndFlush($credit);
        }
    }
}
