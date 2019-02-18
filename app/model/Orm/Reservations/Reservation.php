<?php

namespace App\Model\Orm\Reservations;

use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\ReservationTokens\ReservationToken;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id                                {primary}
 * @property int $state                             {enum self::STATE_*} {default self::STATE_NORMAL}
 * @property DateTime $from
 * @property DateTime $to
 * @property int $price
 * @property string $rz
 * @property DateTime|NULL $originAt
 * @property int|NULL $originPrice
 * @property DateTime|NULL $notifiedAt
 * @property DateTime $createdAt                    {default now}
 *
 * @property Reservation|NULL $extended             {1:1 Reservation::$extendedBy, isMain=true}
 * @property Reservation|NULL $extendedBy           {1:1 Reservation::$extended}
 * @property User|ManyHasOne|NULL $user             {m:1 User::$reservations}
 * @property Place|ManyHasOne $place                {m:1 Place::$reservations}
 * @property UseofPlaces|ManyHasOne $useofPlaces    {m:1 UseofPlaces::$reservations}
 * @property Order|ManyHasOne $order                {m:1 Order::$reservations}
 * @property ReservationToken[]|OneHasMany $tokens  {1:m ReservationToken::$reservation}
 *
 * @property-read ReservationToken|NULL $releaseToken      {virtual}
 * @property-read ReservationToken|NULL $extendToken       {virtual}
 * @property-read ReservationToken|NULL $extendSpareToken  {virtual}
 */
class Reservation extends AbstractEntity
{

    // States
    //NORMAL, RELEASED, EXTENDED, RESERVED

    //standardní rezervace
    const STATE_NORMAL = 1;
    //částečně uvolněná rezervace (to_date se zkratí)
    const STATE_RELEASED = 2;
    //plně uvolněná rezervace (from a to se nemění)
    const STATE_FULL_RELEASED = 7;
    //nová nouzově prodloužená rezervace
    const STATE_EXTENDED = 3;
    //nová nouzově prodloužená rezervace, čekající na zaplacení
    const STATE_WAITING_EXTEND = 8;
    //rezervace rezervovaná vyblokovanému uživateli, kterou však ještě neodmítnul, pokud ji odmítne tak se nastaví na 6, pokud ji příjme tak se nastaví 1
    const STATE_RESERVED = 4;
    //pokud byla rezervace vyblokována nouzovým prodloužením
    const STATE_CANCELLED = 5;
    //pokud mu byla vyblokovana rezervace a on odmít nahradní, nebo nahradni nebyla k dispozici
    const STATE_CANCELLED_AND_REFUND = 6;

    //storno provedl admin
    const STATE_ADMIN_REFUNDED = 20;

    /**
     * @return ReservationToken|NULL
     */
    protected function getterReleaseToken()
    {
        return $this->tokens->get()->getBy(['type' => ReservationToken::TYPE_RELEASE]);
    }

    /**
     * @return ReservationToken|NULL
     */
    protected function getterExtendToken()
    {
        return $this->tokens->get()->getBy(['type' => ReservationToken::TYPE_EXTEND]);
    }

    /**
     * @return ReservationToken|NULL
     */
    protected function getterExtendSpareToken()
    {
        return $this->tokens->get()->getBy(['type' => ReservationToken::TYPE_EXTEND_SPARE]);
    }

    /**
     * Called before persist
     */
    protected function onBeforePersist()
    {
        parent::onBeforePersist();
        if ($this->order === NULL) {
            throw new InvalidStateException('Order cannot be NULL');
        }
    }

}
