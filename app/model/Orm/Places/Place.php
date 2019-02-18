<?php

namespace App\Model\Orm\Places;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Inspections\Inspection;
use App\Model\Orm\Maintenances\Maintenance;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id                                   {primary}
 * @property string|NULL $name
 * @property string|NULL $description
 * @property int $currency                             {enum self::CURRENCY_*} {default self::CURRENCY_CZK}
 * @property string|NULL $streetName
 * @property string $identifier
 * @property string $smsKeyword
 * @property float $lat
 * @property float $lng
 * @property int $locationRadius                       {default 1}
 * @property DateTime|NULL $deletedAt
 * @property int $spareSignature                       {default 0}
 * @property int $capacity                             {default 1}
 * @property bool $countFreePlaces                     {default false}
 * @property string|NULL $members
 * @property Place|NULL $sparePlace                    {m:1 Place::$sparePlaces}
 * @property Organization|ManyHasOne $organization     {m:1 Organization::$places}
 * @property Inspection[]|OneHasMany $inspections      {1:m Inspection::$place}
 * @property Maintenance[]|OneHasMany $maintenances    {1:m Maintenance::$place}
 * @property Reservation[]|OneHasMany $reservations    {1:m Reservation::$place}
 * @property OneHasMany|Place[] $sparePlaces           {1:m Place::$sparePlace}
 * @property OneHasMany|User[] $users                  {1:m User::$place}
 * @property OneHasMany|UseofPlaces[] $useofPlaces     {1:m UseofPlaces::$place}
 * @property OneHasMany|Position[] $positions          {1:m Position::$place}
 */
class Place extends AbstractEntity
{

    // Add extra features
    use PlaceExtras;

    const CURRENCY_CZK = 1;
    const CURRENCY_EUR = 2;
    const CURRENCY_PLN = 3;


    /*
    protected function getterVariation()
    {

        if ($this->type == self::TYPE_ALL && $this->advanceReservation == 1 && $this->capacity == 1)
            return self::VARIATION_PROSPECT;

        if ($this->type == self::TYPE_RESIDENT && $this->advanceReservation == 1 && $this->capacity == 1)
            return self::VARIATION_RESIDENT;

        if ($this->type == self::TYPE_HANDICAPPED && $this->advanceReservation == 1 && $this->capacity == 1)
            return self::VARIATION_HANDICAPPED;

        if ($this->type == self::TYPE_ALL && $this->maxReservationTime > 0 && $this->pricePerUnit == 0)
            return self::VARIATION_TIMEDISC;

        if ($this->type == self::TYPE_ALL && $this->capacity > 1 && $this->pricePerUnit == 0 && $this->advanceReservation == 0)
            return self::VARIATION_FREEZONE;

        if ($this->type == self::TYPE_ALL && $this->capacity > 1 && $this->pricePerUnit > 0 && $this->advanceReservation == 0)
            return self::VARIATION_AUTOMAT;

        throw new ApplicationException("INVALID PLACE VARIATION");
    }
    */

}
