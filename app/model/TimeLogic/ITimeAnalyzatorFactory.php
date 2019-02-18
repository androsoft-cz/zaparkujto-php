<?php

namespace App\Model\TimeLogic;

use App\Model\Search\SearchArgs;

interface ITimeAnalyzatorFactory
{

    /**
     * @param SearchArgs $search
     * @return TimeAnalyzator
     */
    public function create(SearchArgs $search);
}
