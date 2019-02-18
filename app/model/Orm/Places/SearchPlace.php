<?php

namespace App\Model\Orm\Places;

use App\Model\Orm\UseofPlaces\UseofPlaces;

/**
 * @property float $distance
 */
final class SearchPlace extends Place
{
    /** @var int */
    public $freeCapacity;

    /** @var UseofPlaces */
    public $matchingUseofPlace;
}
