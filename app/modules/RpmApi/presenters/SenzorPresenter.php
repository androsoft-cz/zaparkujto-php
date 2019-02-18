<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\SenzorLogs\SenzorLog;
use App\Model\Orm\SenzorLogs\SenzorLogsRepository;
use Nette\Http\Response;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class SenzorPresenter extends BasePresenter
{
    /** @var SenzorLogsRepository @inject */
    public $senzorLogsRepository;

    /** @var PositionsRepository @inject */
    public $positionsRepository;


    public function actionReadAll($data)
    {
        Debugger::log($data, Debugger::DEBUG);
        die;
    }


    public function actionCreate($data)
    {
        Debugger::log($data, Debugger::DEBUG);

        try {
            $json = \Nette\Utils\Json::decode($data);
        } catch (\Nette\Utils\JsonException $e) {
            $json = FALSE;
        }

        if ($json && !empty($json->time)) {
            $dateTime = new DateTime;
            $dateTime->setTimestamp($json->time);
        } else {
            $dateTime = new DateTime;
        }

        try {
            $result = 'Invalid json';

            if ($json) {
                $result = 'Device not found';
                $device = isset($json->device) ? $json->device : '';
                $lat = isset($json->lat) ? $json->lat : '';
                $lng = isset($json->lng) ? $json->lng : '';
                $status = Position::SENZOR_STATUS_UNDEFINED;

                if (isset($json->data->status)) {
                    if ($json->data->status) {
                        $status = Position::SENZOR_STATUS_FULL;
                    } else {
                        $status = Position::SENZOR_STATUS_FREE;
                    }
                }

                /** @var Position $position */
                $position = $this->positionsRepository->getBy([
                    'senzorNumber' => $device,
                ]);

                if ($position) {
                    $result = 'OK';
                    $position->senzorStatus = $status;
                    $position->senzorStatusChangedAt = $dateTime;

                    $this->positionsRepository->persistAndFlush($position);

                    $senzorLog = new SenzorLog;
                    $senzorLog->position = $position;
                    $senzorLog->device = $device;
                    $senzorLog->lat = $lat;
                    $senzorLog->lng = $lng;
                    $senzorLog->status = $status;
                    $senzorLog->createdAt = $dateTime;

                    $this->senzorLogsRepository->persistAndFlush($senzorLog);
                }
            }

            $this->sendApiResponse([$result]);
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }

}
