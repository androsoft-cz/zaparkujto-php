<?php

namespace App\Model\Orm;

use App\Core\Application;

abstract class AbstractFacade
{

    /** @var Application */
    protected $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

}
