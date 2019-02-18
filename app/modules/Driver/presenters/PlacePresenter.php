<?php

namespace App\Modules\Driver;

use App\Model\Facade\PlaceFacade;
use App\Model\Orm\Places\Place;
use App\Modules\Driver\Components\GoogleMap\DetailControl;
use App\Modules\Driver\Components\GoogleMap\IDetailControlFactory;

class PlacePresenter extends BasePresenter
{

    /** @var PlaceFacade @inject */
    public $placeFacade;

    /** @var IDetailControlFactory @inject */
    public $googleMapDetailControlFactory;

    /** @var Place */
    private $place;

    /**
     * GOOGLE ******************************************************************
     */

    /**
     * @param int $id
     */
    public function actionGoogle($id)
    {
        $this->place = $this->placeFacade->getPlace($id);
        if (!$this->place) {
            $this->error('Place not found');
        }
    }

    public function renderGoogle()
    {
        $this->template->place = $this->place;
    }

    /**
     * COMPONENTS **************************************************************
     */

    /**
     * @return DetailControl
     */
    protected function createComponentGoogleMapDetail()
    {
        return $this->googleMapDetailControlFactory->create($this->place);
    }

}
