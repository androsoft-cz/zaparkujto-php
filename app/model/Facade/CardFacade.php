<?php

namespace App\Model\Facade;

use App\Model\Orm\TimeWindows\TimeWindowsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;

final class CardFacade
{
    /** @var UsersRepository */
    private $usersRepository;

    /** @var TimeWindowsRepository */
    private $timeWindowsRepository;


    /**
     * @param UsersRepository $usersRepository
     * @param TimeWindowsRepository $timeWindowsRepository
     */
    public function __construct(
        UsersRepository $usersRepository,
        TimeWindowsRepository $timeWindowsRepository
    ) {
        $this->usersRepository = $usersRepository;
        $this->timeWindowsRepository = $timeWindowsRepository;
    }


    /**
     * @param User $user
     * @return bool
     */
    public function isCurrentlyShareable(User $user)
    {
        if (!$user->isShareableEnabled()) {
            return FALSE;
        }

        if ($user->manualShareable && $user->shareable) {
            return TRUE;
        }

        $currentTimeWindows = $this->timeWindowsRepository->findCurrentByUser($user->id);

        return (bool) $currentTimeWindows->count();
    }

}
