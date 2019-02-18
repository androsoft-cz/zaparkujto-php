<?php

namespace App\Core\Grids;

use App\Core\Grids\Grido\Columns\BinaryDays;
use App\Core\Grids\Grido\Columns\Boolean;
use App\Core\Grids\Grido\Columns\CustomLabeledNumber;
use App\Core\Grids\Grido\Columns\Time;
use App\Core\Grids\Grido\Columns\Weekday;
use App\Core\Utils\WeekdayTranslator;
use Grido\Grid;
use Nette\Application\UI\Control;
use Nextras\Dbal\Connection;

abstract class BaseGrid extends Grid
{

    /** @var \Kdyby\Translation\Translator */
    public $translator;

    /** @var WeekdayTranslator */
    public $weekdayTranslator;

    /** @var Connection $connection */
    public $connection;

    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        Connection $connection
    )
    {
        parent::__construct();

        $this->translator = $translator;
        $this->weekdayTranslator = $weekdayTranslator;
        $this->connection = $connection;
    }

    /**
     * @param Control $control
     * @return void
     */
    abstract public function create(Control $control);

    /**
     * @param mixed $control
     * @return void
     */
    protected function attached($control)
    {
        parent::attached($control);

        if ($control instanceof Control) {
            $this->create($control);
            $cutomization = new \Grido\Customization($this);
            $cutomization->useTemplateBootstrap();
        }
    }

    /**
     * @param string $name
     * @param string $label
     * @return bool
     */
    public function addColumnBoolean($name, $label)
    {
        $column = new Boolean($this, $name, $label);
        $column->headerPrototype->class[] = 'center';

        return $column;
    }

    /**
     * @param string $name
     * @param string $label
     * @return bool
     */
    public function addColumnTime($name, $label)
    {
        $column = new Time($this, $name, $label);
        $column->headerPrototype->class[] = 'center';

        return $column;
    }

    /**
     * @param string $name
     * @param string $label
     * @return bool
     */
    public function addColumnWeekday($name, $label)
    {
        $column = new Weekday($this, $name, $label, $this->weekdayTranslator);

        return $column;
    }

    /**
     * @param string $name
     * @param string $label
     * @return bool
     */
    public function addColumnBinaryDays($name, $label)
    {
        $column = new BinaryDays($this, $name, $label, $this->weekdayTranslator);

        return $column;
    }

    /**
     * @param string $name
     * @param string $label
     * @param int $decimals number of decimal points
     * @param string $decPoint separator for the decimal point
     * @param string $thousandsSep thousands separator
     * @return CustomLabeledNumber
     */
    public function addColumnCustomLabeledNumber($name, $label, $decimals = NULL, $decPoint = NULL, $thousandsSep = NULL)
    {
        return new CustomLabeledNumber($this, $name, $label, $decimals, $decPoint, $thousandsSep);
    }

}
