<?php

namespace App\Model\Orm\Orders;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\PaymentCards\PaymentCard;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * @property int                      $id                    {primary}
 * @property int                      $state                 {enum self::STATE_*}
 * @property int                      $paymentType           {enum self::PAYMENT_TYPE_*}
 * @property string                   $vs
 * @property string|NULL              $email
 * @property string|NULL              $paymentId
 * @property string|NULL              $gatewayUrl
 * @property int|NULL                 $payeeId
 * @property bool                     $authorizationPayment  {default false}
 * @property string|NULL              $gatewayResult
 * @property DateTime|NULL            $paidAt
 * @property DateTime                 $createdAt             {default now}
 *
 * @property ManyHasOne|User|NULL     $user                  {m:1 User::$orders}
 * @property OneHasMany|Reservation[] $reservations          {1:m Reservation::$order}
 * @property OneHasMany|Credit[]      $credits               {1:m Credit::$order}
 * @property ManyHasOne|NULL          $paymentCard           {m:1 PaymentCard::$orders}
 *
 * @property-read int                 $price                 {virtual}
 */
class Order extends AbstractEntity
{
    //objednávka vytvořena, ale není zaplacena
    const STATE_CREATED = 1;
    //objednávka čeká na zaplacení, tzn, že ihned po CREATED se nastaví na WAITING
    const STATE_WAITING = 2;
    //objednávka je plně zaplacena
    const STATE_PAID = 3;
    //objednávka je stornována = pokud GoPay vrátí CANCELLED, nebo TIMEOUTED
    const STATE_STORNO = 4;
    //pokud z GOPAY příjde čeká na platbu
    const STATE_PROCESSING = 5;
    //plně vráceno plátci
    const STATE_FULL_REFUNDED = 6;
    //když částečně vrátíme za částečné uvolnění
    const STATE_PARTIALY_REFUNDED = 7;
    const PAYMENT_TYPE_GATE = 1;
    const PAYMENT_TYPE_CREDIT = 2;
    const PAYMENT_TYPE_FREE = 3;
    const PAYMENT_TYPE_SMS = 4;

    /** @var int */
    private $price;

    /** @var VsProvider */
    private $vsProvider;


    /**
     * @param VsProvider $vsProvider
     */
    public function injectDependencies(VsProvider $vsProvider)
    {
        $this->vsProvider = $vsProvider;
    }


    /**
     * @return int
     */
    protected function getterPrice()
    {
        $totalprice = 0;
        foreach ($this->reservations as $reservation) {
            $totalprice += $reservation->price;
        }

        return $totalprice;
    }


    /**
     * Called before persist
     */
    protected function onBeforeInsert()
    {
        parent::onBeforeInsert();

        if (!$this->hasValue('vs')) {
            $this->vs = $this->vsProvider->get($this);
        }
    }


    /**
     * @return Order
     */
    public function toClone()
    {
        $order = new Order();
        $order->state = Order::STATE_CREATED;
        $order->user = $this->user;
        $order->paymentType = $this->paymentType;

        return $order;
    }
}
