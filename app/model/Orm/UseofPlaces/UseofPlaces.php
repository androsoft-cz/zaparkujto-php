<?php

namespace App\Model\Orm\UseofPlaces;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Utils\BinaryWeekDayCalculator;
use DateInterval;
use Exception;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int $id                                   {primary}
 * @property int $type                                 {enum self::TYPE_*} {default self::TYPE_PROSPECT}
 * @property DateTime|NULL $deletedAt
 * @property DateInterval|NULL $validFrom
 * @property DateInterval|NULL $validTo
 * @property int $validHoursInverted                   {default 0}
 * @property int $validDays                            {default 127}
 * @property int $advanceReservation                   {default 1}
 * @property int $maxReservationTime                   {default 0}
 * @property float $pricePerUnit
 * @property float $pricePerExtend
 * @property int $reservedForZaparkujto                {default 0}
 *
 * @property Place $place                              {m:1 Place::$useofPlaces}
 * @property Reservation[]|OneHasMany $reservations    {1:m Reservation::$useofPlaces}
 */
class UseofPlaces extends AbstractEntity
{
    const TYPE_PROSPECT = 1; //Rezervace|placené místo s rezervací předem // todo zrusit
    const TYPE_RESIDENT = 2; //Rezidenti|místo určené pouze pro rezidenty
    const TYPE_HANDICAPPED = 3; //Invalidé|místo určené pouze pro invalidy
    const TYPE_AUTOMAT = 4; //Placené|klasické placené parkovací místo
    const TYPE_TIMEDISC = 5; //Kotouč|parkovací místo s omezenou dobou parkování
    const TYPE_SELECTED = 6; //Vyhrazené|místo rezervované pouze pro určené SPZ
    const TYPE_FREEZONE = 7; //Zdarma|parkoviště zdarma
    const TYPE_SHAREABLE = 8; //Sdílené|chytré sdílené rezidentní místo
    const TYPE_SMSZONE = 9; //SMSZóna|Oblast ve které platí stejné SMS podmínky pro placení

    const TYPE_SMART_STAND = 21; //Stojan pro vlastní kolo s možností zamknutí
    const TYPE_SHARED_VEHICLE = 22; //Sdílené kolo (nezáleží kdo jej vlastní, ale můžeš si ho půjčit)

    const TYPE_NOT_PROSPECT = 101; // todo zrusit

        /**
         * @return bool
         */
    public function isForFree()
    {
        return $this->type == UseofPlaces::TYPE_HANDICAPPED || $this->type == UseofPlaces::TYPE_TIMEDISC || $this->type == UseofPlaces::TYPE_FREEZONE;
    }


    /**
     * @param int $day  1-7, 1 = monday
     * @return bool
     * @throws Exception
     */
    public function isValidDay($day)
    {
        return BinaryWeekDayCalculator::getDayValue($this->validDays, $day);
    }


    /**
     * @param int $day  1-7, 1 = monday
     * @param bool $value
     * @throws Exception
     */
    public function setValidDay($day, $value)
    {
        $this->validDays = BinaryWeekDayCalculator::calculateBinary($this->validDays, $day, $value);
    }
}
