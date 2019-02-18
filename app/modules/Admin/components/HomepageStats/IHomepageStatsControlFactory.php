<?php

namespace App\Modules\Admin\Components\HomepageStats;

interface IHomepageStatsControlFactory
{

    /**
     * @return HomepageStatsControl
     */
    public function create();
}
