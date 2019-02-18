<?php

namespace App\Model\Reservations;

use App\Model\Orm\Users\User;
use Nette\Application\UI\Presenter;

class PlaceReservatorContext
{
    /** @var bool */
    public $isCheck = FALSE;

    /** @var int */
    public $placeId;

    /** @var int|NULL  Reserve parking from time*/
    public $reserveFrom;

    /** @var int|NULL  Reserve parking to time*/
    public $reserveTo;

    /** @var int|NULL  Reserve parking for parkingTime minutes */
    public $parkingTime;

    /** @var string */
    public $rz;

    /**
     * @var mixed
     * TRUE: User want to use new (no stored) payment card & store it
     * FALSE: User want to use new (no stored) payment card & not to store it
     * INT: User want to use already stored payment card (by it's ID)
     */
    public $usePaymentCard;

    /** @var User */
    public $user;

    /** @var Presenter */
    public $presenter;

}
