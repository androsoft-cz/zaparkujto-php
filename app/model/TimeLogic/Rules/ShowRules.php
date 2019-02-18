<?php

namespace App\Model\TimeLogic\Rules;

use App\Model\Orm\Places\Place;
use Nette\Utils\DateTime;

final class ShowRules extends BaseRules
{

    /**
     * @param Place $place
     * @return bool
     */
    public function isShowable(Place $place)
    {
        // Current reservation
        $current = $this->getCurrentReservation($place);
        if ($current) {
            $minutes = $this->config->expand('logic.show_max_reservation_end');
            $date = $this->getSearchDate()->modifyClone("+ $minutes minutes");
            if ($current->to > $date) {
                return FALSE;
            }
        }

        // Next reservation
        $next = $this->getNextReservation($place);
        if ($next) {
            $minutes = $this->config->expand('logic.show_min_reservation_start');
            $date = $this->getSearchDate()->modifyClone("+ $minutes minutes");
            if ($next->from < $date) {
                return FALSE;
            }
        }

        // Compare current and next reservation
        if ($current && $next) {
            $minimalDiff = $this->config->expand('diff.minimal');
            if (DateTime::from($current->to)->modify("+ $minimalDiff minutes") >= $next->from) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
