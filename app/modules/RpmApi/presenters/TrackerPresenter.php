<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\SenzorLogs\SenzorLog;
use App\Model\Orm\SenzorLogs\SenzorLogsRepository;
use Nette\Http\Response;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class TrackerPresenter extends BasePresenter
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
        die;
    }

}
