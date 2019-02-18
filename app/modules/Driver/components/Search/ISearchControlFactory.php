<?php

namespace App\Modules\Driver\Components\Search;

interface ISearchControlFactory
{

    /**
     * @return SearchControl
     */
    public function create();

}
