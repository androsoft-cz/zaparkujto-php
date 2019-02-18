<?php

namespace App\Model\Orm\Users;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\ApiTokens\ApiToken;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\PaymentCards\PaymentCard;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Inspections\Inspection;
use App\Model\Orm\Maintenances\Maintenance;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Prescriptions\Prescription;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\TimeWindows\TimeWindow;
use App\Model\Orm\UsersRz\UserRz;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int                      $id                                {primary}
 * @property string                   $username
 * @property-read string              $password
 * @property string|NULL              $role
 * @property string|NULL              $name
 * @property DateTime|NULL            $loggedAt
 * @property int|NULL                 $type                              {enum self::TYPE_*}
 * @property int                      $handicapped                       {default 0}
 * @property int                      $manualShareable                   {default 0}
 * @property int                      $shareable                         {default 0}
 * @property DateTime                 $createdAt                         {default now}
 * @property string|NULL              $passwordResetToken
 * @property DateTime|NULL            $passwordResetTokenGeneratedAt
 *
 * @property Organization|NULL        $organization                      {m:1 Organization::$users}
 * @property Contact|NULL             $contact                           {1:1 Contact::$user, isMain=true}
 * @property Place|NULL               $place                             {m:1 Place::$users}
 * @property Inspection[]|OneHasMany  $inspections                       {1:m Inspection::$user}
 * @property Maintenance[]|OneHasMany $maintenances                      {1:m Maintenance::$user}
 * @property Reservation[]|OneHasMany $reservations                      {1:m Reservation::$user}
 * @property Order[]|OneHasMany       $orders                            {1:m Order::$user}
 * @property Prescription[]|OneHasMany $prescriptions                    {1:m Prescription::$user}
 * @property ApiToken[]|OneHasMany    $apiTokens                         {1:m ApiToken::$user}
 * @property Credit[]|OneHasMany      $credits                           {1:m Credit::$user}
 * @property TimeWindow[]|OneHasMany  $timeWindows                       {1:m TimeWindow::$user}
 * @property UserRz[]|OneHasMany      $userRzs                           {1:m UserRz::$user}
 * @property OneHasMany|PaymentCard[] $paymentCards                      {1:m PaymentCard::$user}
 *
 * @property-read int                 $creditAmount                      {virtual}
 */
class User extends AbstractEntity
{
    /**
     * Types
     */
    const TYPE_PROSPECT = 1;
    const TYPE_RESIDENT = 2;
    const TYPE_SMART = 3;
    const TYPE_MOJEOBEC = 10;
    // Dočasné pravidlo: user může být rezidentem pouze v jednom městě a proto nám stačí vazba na organization a typ uživatele

    /** @var int */
    private $creditAmount;


    /**
     * @return int
     */
    protected function getterCreditAmount()
    {
        $totalCredit = 0;
        foreach ($this->credits as $credit) {
            $totalCredit += $credit->price;
        }

        return $totalCredit;
    }


    /**
     * @param string $password
     * @return string
     */
    public function setPassword($password)
    {
        return $this->setRawValue('password', Passwords::hash($password));
    }


    /**
     * @return array
     */
    public function toIdentity()
    {
        return [
            'username' => $this->username,
            'name' => $this->name,
            'loggedAt' => $this->loggedAt,
            'createdAt' => $this->createdAt,
            'role' => $this->role,
            'organization' => $this->organization ? $this->organization->toArray(self::TO_ARRAY_RELATIONSHIP_AS_ID) : NULL,
        ];
    }


    /**
     * @return bool
     */
    public function isShareableEnabled()
    {
        if (!$this->place) {
            return FALSE;
        }

        foreach ($this->place->useofPlaces as $useofPlace) {
            if ($useofPlace->type == UseofPlaces::TYPE_SHAREABLE) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
