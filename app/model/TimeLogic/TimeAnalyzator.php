<?php

namespace App\Model\TimeLogic;

use App\Core\Config\Config;
use App\Model\Orm\Places\Place;
use App\Model\Search\SearchArgs;
use App\Model\TimeLogic\Rules\ShowRules;
use App\Model\TimeLogic\Rules\TimeRules;

final class TimeAnalyzator
{

    /** @var Config */
    private $config;

    /** @var SearchArgs */
    private $search;

    /** @var ShowRules */
    private $showRules;

    /** @var TimeRules */
    private $timeRules;

    /**
     * @param Config $config
     * @param SearchArgs $search
     */
    public function __construct(Config $config, SearchArgs $search)
    {
        $this->config = $config;
        $this->search = $search;

        $this->showRules = new ShowRules($config, $search);
        $this->timeRules = new TimeRules($config, $search);
    }

    /**
     * SHOW-RULES **************************************************************
     */

    /**
     * @param Place $place
     * @return bool
     */
    public function isShowable(Place $place)
    {
        return $this->showRules->isShowable($place);
    }

    /**
     * TIME-RULES **************************************************************
     */

    /**
     * @param Place $place
     * @return FreeEntry
     */
    public function getFreeEntry(Place $place)
    {
        return $this->timeRules->getFreeEntry($place);
    }

    /**
     * @param Place $place
     * @return ParkEntry
     */
    public function getParkEntry(Place $place)
    {
        return $this->timeRules->getParkEntry($place);
    }

}
