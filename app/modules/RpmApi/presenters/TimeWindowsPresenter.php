<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\TimeWindows\TimeWindow;
use App\Model\Orm\TimeWindows\TimeWindowsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use DateInterval;
use Nette\Http\Response;

class TimeWindowsPresenter extends SecurePresenter
{

    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var TimeWindowsRepository @inject */
    public $timeWindowsRepository;


    public function actionReadAll()
    {
        $timeWindows = $this->timeWindowsRepository->findByUserSorted($this->user->id);
        $results = [];

        foreach ($timeWindows as $timeWindow) {
            $results[] = [
                'id' => $timeWindow->id,
                'weekday' => $timeWindow->weekday,
                'begin' => $timeWindow->begin->format('%H:%I'),
                'end' => $timeWindow->end->format('%H:%I'),
            ];
        }

        $this->sendApiResponse($results);
    }


    /**
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

            $timeWindow = new TimeWindow();
            $timeWindow->weekday = (int) $d->weekday;

            list($hours, $minutes) = sscanf($d->begin, '%d:%d');
            $timeWindow->begin = new DateInterval("PT{$hours}H{$minutes}M0S");

            list($hours, $minutes) = sscanf($d->end, '%d:%d');
            $timeWindow->end = new DateInterval("PT{$hours}H{$minutes}M0S");

            $timeWindow->user = $user;

            $this->timeWindowsRepository->persistAndFlush($timeWindow);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);
        } catch (\ErrorException $e) {
            $this->sendError('Error while saving data!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }


    /**
     * @param int $id
     * @param mixed $data
     */
    public function actionUpdate($id, $data)
    {
        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);

            /** @var TimeWindow $timeWindow */
            $timeWindow = $this->timeWindowsRepository->getById($id);

            if ($timeWindow->user->id != $user->id) {
                throw new \ErrorException('User is not owner of the time window.');
            }

            $timeWindow->weekday = (int) $d->weekday;

            list($hours, $minutes) = sscanf($d->begin, '%d:%d');
            $timeWindow->begin = new DateInterval("PT{$hours}H{$minutes}M0S");

            list($hours, $minutes) = sscanf($d->end, '%d:%d');
            $timeWindow->end = new DateInterval("PT{$hours}H{$minutes}M0S");

            $this->timeWindowsRepository->persistAndFlush($timeWindow);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);
        } catch (\ErrorException $e) {
            $this->sendError('Error while saving data!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }


    /**
     * @param int $id
     */
    public function actionDelete($id)
    {
        try {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->user->id);

            /** @var TimeWindow $timeWindow */
            $timeWindow = $this->timeWindowsRepository->getById($id);

            if ($timeWindow->user->id != $user->id) {
                throw new \ErrorException('User is not owner of the time window.');
            }

            $this->timeWindowsRepository->delete($timeWindow);

            $results = [
                'result' => 'ok',
            ];

            $this->sendApiResponse($results);
        } catch (\ErrorException $e) {
            $this->sendError('Error while saving data!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

}
