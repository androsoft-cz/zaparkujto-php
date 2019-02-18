<?php

namespace App\Model\Orm\ReservationTokens;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Reservations\Reservation;
use Nette\Utils\DateTime;

/**
 * @property int $id                    {primary}
 * @property int $type                  {enum self::TYPE_*}
 * @property string $token
 * @property DateTime $createdAt        {default now}
 * @property DateTime|NULL $expiredAt
 * @property DateTime|NULL $appliedAt
 * @property Reservation $reservation   {m:1 Reservation::$tokens}
 */
class ReservationToken extends AbstractEntity
{

    // Types = na co ten token je
    //token pro uvolnění
    const TYPE_RELEASE = 1;
    //token pro prodloužení
    const TYPE_EXTEND = 2;
    //token pro přijetí/odmítnutí náhradního místa
    const TYPE_EXTEND_SPARE = 3;

}
