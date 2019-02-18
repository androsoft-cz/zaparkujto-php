<?php

namespace App\Core\Grids\Grido\Columns;

use Grido\Components\Columns\Number;

class CustomLabeledNumber extends Number
{

    /** @var string|NULL */
    private $customLabel;


    /**
     * @param string $customLabel
     */
    public function setCustomLabel($customLabel)
    {
        $this->customLabel = $customLabel;
    }


    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        if ($this->customLabel !== NULL) {
            return $this->customLabel;
        }

        return parent::getLabel();
    }
}
