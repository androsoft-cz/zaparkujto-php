<?php

namespace App\Core\Grids\Grido\Columns;

use DateInterval;
use Grido\Components\Columns\Text;

class Time extends Text
{

    public function getCellPrototype($row = NULL)
    {
        $cell = parent::getCellPrototype($row = NULL);
        $cell->class[] = 'center';

        return $cell;
    }


    /**
     * @param DateInterval|int $value
     * @return \Nette\Utils\Html
     */
    protected function formatValue($value)
    {
        $html = new \Nette\Utils\Html();

        if ($value instanceof DateInterval) {
            $html->setText($value->format('%H:%I'));
        } else {$hours = floor($value / 60);
            $minutes = floor($value % 60);
            $html->setText(sprintf('%02d:%02d', $hours, $minutes));
        }

        return $html;
    }
}
