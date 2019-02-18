<?php

namespace App\Model\Payment;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\PaymentCards\PaymentCard;
use Nette\Application\LinkGenerator;
use OndraKoupil\Csob\Client;
use OndraKoupil\Csob\Config;
use OndraKoupil\Csob\Extension;
use OndraKoupil\Csob\Extensions\CardNumberExtension;
use OndraKoupil\Csob\GatewayUrl;
use OndraKoupil\Csob\Payment;
use stdClass;

class CsobService implements IPaymentGatewayService
{
    const PUBLIC_KEYFILE_BANK_TEST = '../app/model/Payment/CSOB_cert/mips_iplatebnibrana.csob.cz.pub';
    const PUBLIC_KEYFILE_BANK_PRODUCTION = '../app/model/Payment/CSOB_cert/mips_platebnibrana.csob.cz.pub';
    const SHOP_NAME = 'Zaparkujto.cz';
    const STATUS_PAYMENT_CREATED = 1;
    const STATUS_PAYMENT_INPROCESS = 2;
    const STATUS_PAYMENT_CANCELED = 3;
    const STATUS_PAYMENT_CONFIRMED = 4;
    const STATUS_PAYMENT_REVOKED = 5;
    const STATUS_PAYMENT_DENIED = 6;
    const STATUS_PAYMENT_CHARGE_WAITING = 7;
    const STATUS_PAYMENT_CHARGED = 8;
    const STATUS_PAYMENT_PAYBACK_INPROCESS = 9;
    const STATUS_PAYMENT_PAYBACK_PROCESSED = 10;

    /** @var Client */
    private $csobClient;


    public function __construct(stdClass $csobSettings, LinkGenerator $linkGenerator)
    {
        $config = new Config(
            $csobSettings->merchantId,
            $csobSettings->privateKeyFile,
            $csobSettings->testMode ? self::PUBLIC_KEYFILE_BANK_TEST : self::PUBLIC_KEYFILE_BANK_PRODUCTION,
            self::SHOP_NAME,
            $linkGenerator->link('Driver:Order:processCsobPay'),
            $csobSettings->testMode ? GatewayUrl::TEST_1_7 : GatewayUrl::PRODUCTION_1_7,
            $csobSettings->privateKeyPassword ?: NULL
        );
        $this->csobClient = new Client($config);
    }


    public function createSimplePayment(array $payment, int $orderId): array
    {
        $csobPayment = $this->preparePayment($payment, $orderId);
        $this->csobClient->paymentInit($csobPayment);

        return [
            'gw_url' => $this->csobClient->getPaymentProcessUrl($csobPayment),
            'id' => $csobPayment->getPayId(),
        ];
    }


    public function createAuthorizationPayment(array $payment, int $orderId): array
    {
        $csobPayment = $this->preparePayment($payment, $orderId);
        $csobPayment->setOneClickPayment(TRUE);
        $this->csobClient->paymentInit($csobPayment);

        return [
            'gw_url' => $this->csobClient->getPaymentProcessUrl($csobPayment),
            'id' => $csobPayment->getPayId(),
        ];
    }


    public function createRecurringPayment(array $payment, int $orderId, PaymentCard $paymentCard): array
    {
        $csobPayment = $this->preparePayment($payment, $orderId);
        $this->csobClient->paymentOneClickInit($paymentCard->referencePay->paymentId, $csobPayment);
        $this->csobClient->paymentOneClickStart($csobPayment);

        return [
            'gw_url' => NULL,
            'id' => $csobPayment->getPayId(),
        ];
    }


    public function refundPayment($paymentId, $amount)
    {
        // TODO: Implement refundPayment() method.
    }


    public function verifyPayment(Order $order): array
    {
        $result = [];
        $payer = new stdClass();
        $payer->contact = new stdClass();
        $payer->contact->email = $order->user->username;
        $result['payer'] = $payer;

        $extension = new CardNumberExtension();
        $state = $this->csobClient->paymentStatus($order->paymentId, FALSE, $extension);

        $paymentCard = new stdClass();
        $paymentCard->card_number = $extension->getLongMaskedCln();
        $paymentCard->expiration = $extension->getExpiration();
        $result['payer']->payment_card = $paymentCard;

        switch ($state['paymentStatus']) {
            case self::STATUS_PAYMENT_CONFIRMED:
            case self::STATUS_PAYMENT_CHARGE_WAITING:
            case self::STATUS_PAYMENT_CHARGED:
                $result['state'] = PaymentService::PAYMENT_STATE_PAID;
                break;
            case self::STATUS_PAYMENT_CANCELED:
            case self::STATUS_PAYMENT_REVOKED:
            case self::STATUS_PAYMENT_DENIED:
                $result['state'] = PaymentService::PAYMENT_STATE_CANCELED;
                break;
            case self::STATUS_PAYMENT_CREATED:
            case self::STATUS_PAYMENT_INPROCESS:
            case self::STATUS_PAYMENT_PAYBACK_INPROCESS:
            case self::STATUS_PAYMENT_PAYBACK_PROCESSED:
                $result['state'] = PaymentService::PAYMENT_STATE_NOPAID;
                break;
            default:
                $result['state'] = PaymentService::PAYMENT_STATE_ERROR;
                break;
        }

        return $result;
    }


    private function preparePayment(array $payment, int $orderId): Payment
    {
        $csobPayment = new Payment($payment['order_number']);
        foreach ($payment['items'] as $item) {
            $csobPayment->addCartItem(
                $payment['short_description'],
                1,
                $item['amount'] * 100,
                $item['name']
            );
        }

        // $csobPayment->closePayment = true;
        $csobPayment->currency = $payment['currency'];
        $csobPayment->setMerchantData($orderId);
        return $csobPayment;
    }
}
