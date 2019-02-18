<?php

namespace App\Modules\Driver;

use App\Model\Search\SearchService;

final class ApiPresenter extends BasePresenter
{
    /** @var SearchService @inject */
    public $searchService;


    /**
     * @param string $q
     */
    public function actionSuggestPlace($q)
    {
        $suggestions = $this->searchService->suggest($q);
        $this->sendJson($suggestions);
    }

}
