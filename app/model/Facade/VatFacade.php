<?php

namespace App\Model\Facade;

class VatFacade
{

    /** @var float */
    private $vatPercentage = 21;


    /**
     * @return float
     */
    public function getVatPercentage()
    {
        return $this->vatPercentage;
    }


    /**
     * @param float $taxedPrice
     * @return float
     */
    public function getVat($taxedPrice)
    {
        return round($taxedPrice * $this->vatPercentage / 100, 1);
    }


    /**
     * @param float $taxedPrice
     * @return float
     */
    public function getUntaxedPrice($taxedPrice)
    {
        return round($taxedPrice * (100 - $this->vatPercentage) / 100, 1);
    }

}
