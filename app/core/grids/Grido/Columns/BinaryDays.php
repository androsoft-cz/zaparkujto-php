<?php

namespace App\Core\Grids\Grido\Columns;

use App\Core\Utils\WeekdayTranslator;
use Grido\Components\Columns\Text;
use Grido\Grid;

class BinaryDays extends Text
{
    /** @var WeekdayTranslator */
    private $weekdayTranslator;


    /**
     * @param Grid $grid
     * @param string $name
     * @param string $label
     * @param WeekdayTranslator $weekday
     */
    public function __construct(
        Grid $grid,
        $name,
        $label,
        WeekdayTranslator $weekday
    )
    {
        parent::__construct($grid, $name, $label);
        $this->weekdayTranslator = $weekday;
    }


    /**
     * @param int $value
     * @return \Nette\Utils\Html
     */
    protected function formatValue($value)
    {
        $html = new \Nette\Utils\Html();
        $html->setText($this->weekdayTranslator->translateBinaryDays($value));

        return $html;
    }

}
