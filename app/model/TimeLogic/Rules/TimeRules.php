<?php

namespace App\Model\TimeLogic\Rules;

use App\Model\Orm\Places\Place;
use App\Model\TimeLogic\FreeEntry;
use App\Model\TimeLogic\ParkEntry;
use Nette\Utils\DateTime;

final class TimeRules extends BaseRules
{

    /**
     * @param Place $place
     * @return FreeEntry
     */
    public function getFreeEntry(Place $place)
    {
        if (($current = $this->getCurrentReservation($place))) {
            $minutes = $this->config->expand('logic.tolerance_time');
            $date = DateTime::from($current->to)->modifyClone("+ $minutes minutes");
            if ($date >= $this->getSearchDate()) {
                // FREE IN
                return new FreeEntry(FreeEntry::FREE_IN, $date);
            } else {
                // FREE NOW
                return new FreeEntry(FreeEntry::FREE_NOW, $date);
            }
        }

        if (($prev = $this->getPreviousReservation($place))) {
            $minutes = $this->config->expand('logic.tolerance_time');
            $date = DateTime::from($prev->to)->modifyClone("+ $minutes minutes");
            if ($date > $this->getSearchDate()) {
                // FREE IN
                return new FreeEntry(FreeEntry::FREE_IN, $date);
            }
        }

        // FREE NOW
        return new FreeEntry(FreeEntry::FREE_NOW, $this->getSearchDate());
    }

    /**
     * @param Place $place
     * @return ParkEntry
     */
    public function getParkEntry(Place $place)
    {
        if (($next = $this->getNextReservation($place))) {
            // PARK UNTIL
            $minutes = $this->config->expand('logic.time_park_long_time') + $this->config->expand('logic.tolerance_time');
            if ($next->from < $this->getSearchDate()->modifyClone("+ $minutes minutes")) {
                $minutes = $this->config->expand('logic.tolerance_time');

                return new ParkEntry(ParkEntry::PARK_UNTIL, DateTime::from($next->from)->modifyClone("- $minutes minutes"));
            }

            // PARK LONG
            $minutes = $this->config->expand('logic.time_park_long_time');

            return new ParkEntry(ParkEntry::PARK_LONG, $this->getSearchDate()->modifyClone("+ $minutes minutes"));
        } else {
            // PARK LONG
            $minutes = $this->config->expand('logic.time_park_long_time');

            return new ParkEntry(ParkEntry::PARK_LONG, $this->getSearchDate()->modifyClone("+ $minutes minutes"));
        }
    }

}
