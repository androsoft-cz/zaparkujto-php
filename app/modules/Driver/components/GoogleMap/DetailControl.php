<?php

namespace App\Modules\Driver\Components\GoogleMap;

use App\Core\UI\BaseControl;
use App\Model\Orm\Places\Place;

final class DetailControl extends BaseControl
{

    /** @var Place */
    private $place;

    /**
     * @param Place $place
     */
    public function __construct(Place $place)
    {
        parent::__construct();
        $this->place = $place;
    }

    /**
     * Render template
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . '/templates/detail.latte');
        $this->template->place = $this->place;
        $this->template->render();
    }

}
