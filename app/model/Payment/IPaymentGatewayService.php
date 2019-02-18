<?php

namespace App\Model\Payment;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\PaymentCards\PaymentCard;

interface IPaymentGatewayService
{
    public function createSimplePayment(array $payment, int $orderId);


    public function createAuthorizationPayment(array $payment, int $orderId);


    public function createRecurringPayment(array $payment, int $orderId, PaymentCard $paymentCard);


    public function refundPayment($paymentId, $amount);


    public function verifyPayment(Order $order);
}
