<?php

namespace App\Modules\Rpmapi;

use App\Model\Search\SearchService;
use Nette\Http\Response;

class GeoPresenter extends SecurePresenter
{

    /** @var SearchService @inject */
    public $searchService;

    /**
     * List of places
     */
    public function actionReadAll(array $query)
    {
        try {
            $q = isset($query['q']) ? $query['q'] : NULL;
            $suggestions = $this->searchService->suggest($q);
            $this->sendApiResponse($suggestions);
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
