<?php

namespace App\Model\Orm\Organizations;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\PaymentGateways\PaymentGateway;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Prescriptions\Prescription;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

//TODO: odstranit gopay & paymentSettings
/**
 * @property int                       $id                                        {primary}
 * @property string                    $name
 * @property string                    $slug
 * @property string|NULL               $description
 * @property int|NULL                  $vsPrefix
 * @property string|NULL               $termsAndConditions
 * @property string|NULL               $consentProcessingPersonalData
 * @property int                       $usersCreditGain
 * @property float                     $lat
 * @property float                     $lng
 * @property DateTime|NULL             $deletedAt
 * @property int                       $minIntervalBetweenReservations
 * @property int                       $demo                                      {default 0}
 * @property string|NULL               $smsKeyword
 *
 * @property Contact|NULL              $contact                                   {1:1 Contact::$organization, isMain=true}
 * @property User[]|OneHasMany         $users                                     {1:m User::$organization}
 * @property Place[]|OneHasMany        $places                                    {1:m Place::$organization}
 * @property ManyHasOne|PaymentGateway|NULL $paymentGateway                            {m:1 PaymentGateway::$organizations}
 */
class Organization extends AbstractEntity
{
}
