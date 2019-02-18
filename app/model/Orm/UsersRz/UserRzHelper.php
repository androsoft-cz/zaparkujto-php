<?php

namespace App\Model\Orm\UsersRz;

use App\Model\Orm\Users\User;

final class UserRzHelper
{
    /** @var UsersRzRepository */
    private $usersRzRepository;

    /** @var UsersRzMapper */
    private $usersRzMapper;


    public function __construct(
        UsersRzRepository $usersRzRepository,
        UsersRzMapper $usersRzMapper
    ) {
        $this->usersRzRepository = $usersRzRepository;
        $this->usersRzMapper = $usersRzMapper;
    }


    public function updateRzs(User $user, array $rzs)
    {
        if (empty($rzs)) {
            return;
        }

        $rzs = array_unique($rzs);
        $rzs = array_map('trim', $rzs);
        $rzs = str_replace(' ', '', $rzs);

        $rzsToDelete = $this->usersRzMapper->findRzsToDelete($user, $rzs);

        foreach ($rzsToDelete as $rzToDelete) {
            $this->usersRzRepository->removeAndFlush($rzToDelete->id);
        }

        foreach ($rzs as $rz) {
            if (!$rz) {
                continue;
            }

            $rzExit = $this->usersRzRepository->findBy([
                'this->rz' => $rz,
                'this->user' => $user,
            ])->count();

            if ($rzExit) {
                continue;
            }

            $userRz = new UserRz;
            $userRz->user = $user;
            $userRz->rz = $rz;

            try {
                $this->usersRzRepository->persistAndFlush($userRz);
            } catch (\Nextras\Dbal\UniqueConstraintViolationException $e) {
                $this->usersRzRepository->flush();
            }
        }
    }

}
