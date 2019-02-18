<?php

namespace App\Model\Payment;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\PaymentCards\PaymentCard;
use Markette\GopayInline\Api\Entity\PaymentFactory;
use Markette\GopayInline\Api\Entity\RecurrentPaymentFactory;
use Markette\GopayInline\Api\Entity\RecurringPaymentFactory;
use Markette\GopayInline\Api\Lists\PaymentState;
use Markette\GopayInline\Client;
use Markette\GopayInline\Config;
use Markette\GopayInline\Http\Response;
use Nette\Application\LinkGenerator;
use Nette\Utils\DateTime;
use stdClass;

final class GopayService implements IPaymentGatewayService
{
    /** @var Client */
    private $gopay;

    /** @var LinkGenerator */
    private $linkGenerator;


    public function __construct(stdClass $gopaySettings, LinkGenerator $linkGenerator)
    {
        $gopayConfig = new Config(
            $gopaySettings->gopayId,
            $gopaySettings->clientId,
            $gopaySettings->secretKey,
            $gopaySettings->testMode ? Config::TEST : Config::PROD);

        $this->gopay = new Client($gopayConfig);
        $this->linkGenerator = $linkGenerator;
    }


    public function createSimplePayment(array $payment, int $orderId): Response
    {
        $payment = $this->preparePayment($payment, $orderId);
        return $this->gopay->payments->createPayment(PaymentFactory::create($payment));
    }


    public function createAuthorizationPayment(array $payment, int $orderId): Response
    {
        $payment = $this->preparePayment($payment, $orderId);
        $payment['recurrence'] = [
            'recurrence_cycle' => 'ON_DEMAND',
            'recurrence_date_to' => new DateTime('+50 years'),
        ];
        return $this->gopay->payments->createRecurrentPayment(RecurrentPaymentFactory::create($payment));
    }


    public function createRecurringPayment(array $payment, int $orderId, PaymentCard $paymentCard): Response
    {
        unset($payment['lang'], $payment['payer']);
        return $this->gopay->payments->createRecurringPayment($paymentCard->referencePay->paymentId, RecurringPaymentFactory::create($payment));
    }


    public function refundPayment($paymentId, $amount)
    {
        return $this->gopay->payments->refundPayment($paymentId, $amount);
    }


    public function verifyPayment(Order $order)
    {
        $payment = $this->gopay->payments->verify($order->paymentId);
        switch ($payment['state']) {
            case PaymentState::CREATED:
            case PaymentState::AUTHORIZED:
            case PaymentState::PAYMENT_METHOD_CHOSEN:
                $payment['state'] = PaymentService::PAYMENT_STATE_NOPAID;
                break;
            case PaymentState::PAID:
                $payment['state'] = PaymentService::PAYMENT_STATE_PAID;
                break;
            case PaymentState::CANCELED:
                $payment['state'] = PaymentService::PAYMENT_STATE_CANCELED;
                break;
            case PaymentState::TIMEOUTED:
                $payment['state'] = PaymentService::PAYMENT_STATE_ERROR;
                break;
            case PaymentState::REFUNDED:
            case PaymentState::PARTIALLY_REFUNDED:
                $payment['state'] = PaymentService::PAYMENT_STATE_REFUNDED;
                break;
            default:
                $payment['state'] = PaymentService::PAYMENT_STATE_ERROR;
                break;
        }

        return $payment;
    }


    private function preparePayment(array $payment, int $orderId): array
    {
        $payment['return_url'] = $this->linkGenerator->link('Driver:Order:processGoPay', ['orderId' => $orderId]);
        $payment['notify_url'] = $this->linkGenerator->link('Driver:Order:notifyGoPay', ['orderId' => $orderId]);
        return $payment;
    }
}
