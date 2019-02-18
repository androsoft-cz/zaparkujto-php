<?php

namespace App\Model\Payment;

use App\Model\Exceptions\Runtime\ThepayException;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\PaymentCards\PaymentCard;
use Markette\GopayInline\Api\Lists\PaymentInstrument;
use Nette\Application\LinkGenerator;
use Nette\Http\Url;
use Nette\NotImplementedException;
use stdClass;
use Tp\ReturnedPayment;
use Trejjam\ThePay\MerchantConfig;
use Trejjam\ThePay\Payment;

final class ThePayService implements IPaymentGatewayService
{
    const CHANNEL_RF = 1;
    const CHANNEL_KB = 11;
    const CHANNEL_MB = 12;
    const CHANNEL_GE = 13;
    const CHANNEL_FIO = 17;
    const CHANNEL_WIRE_TRANSFER = 18;
    const CHANNEL_CSOB = 19;
    const CHANNEL_CARD = 21;
    const CHANNEL_CS = 23;
    const STATUS_NO_PAID = 1;
    const STATUS_OK = 2;
    const STATUS_CANCELED = 3;
    const STATUS_ERROR = 4;
    const STATUS_UNDERPAID = 6;
    const STATUS_WAITING = 7;
    const STATUS_STORNO = 8;
    const STATUS_CARD_DEPOSIT = 9;

    /** @var MerchantConfig */
    private $thepayConfig;

    /** @var LinkGenerator */
    private $linkGenerator;


    public function __construct(stdClass $thepayConfig, LinkGenerator $linkGenerator)
    {
        $this->thepayConfig = new MerchantConfig();
        $this->thepayConfig->merchantId = $thepayConfig->merchantId;
        $this->thepayConfig->accountId = $thepayConfig->accountId;
        $this->thepayConfig->password = $thepayConfig->password;
        $this->thepayConfig->dataApiPassword = $thepayConfig->dataApiPassword;
        $this->thepayConfig->isDemo = $thepayConfig->testMode;
        if (!$this->thepayConfig->isDemo()) {
            $this->thepayConfig->gateUrl = 'https://www.thepay.cz/gate/';
            $this->thepayConfig->webServicesWsdl = 'https://www.thepay.cz/gate/api/gate-api.wsdl';
            $this->thepayConfig->dataWebServicesWsdl = 'https://www.thepay.cz/gate/api/data.wsdl';
        }

        $this->linkGenerator = $linkGenerator;
    }


    public function createSimplePayment(array $payment, int $orderId)
    {
        $returnUrl = $this->linkGenerator->link('Driver:Order:processThePay', ['orderId' => $orderId]);
        $backToShopUrl = $this->linkGenerator->link('Driver:Places:default');

        $thepayPayment = new Payment($this->thepayConfig, $this->linkGenerator);
        $thepayPayment->setValue($payment['amount']);
        $thepayPayment->setCustomerEmail($payment['payer']['contact']['email']);
        $thepayPayment->setDescription($payment['order_description']);
        $thepayPayment->setReturnUrl($returnUrl);
        $thepayPayment->setBackToEshopUrl($backToShopUrl);
        $thepayPayment->setMerchantData($payment['order_number']);
        $thepayPayment->setCurrency($payment['currency']);
        $thepayPayment->setMethodId($this->convertPaymentMethod($payment['payer']['default_payment_instrument']));

        $url = $this->buildUrl($thepayPayment);
        return [
            'gw_url' => $url,
            'id' => NULL,
        ];
    }


    public function createAuthorizationPayment(array $payment, int $orderId)
    {
        throw new NotImplementedException('Recurrent payments are not supported.');
    }


    public function createRecurringPayment(array $payment, int $orderId, PaymentCard $paymentCard)
    {
        throw new NotImplementedException('Recurrent payments are not supported.');
    }


    public function refundPayment($paymentId, $amount)
    {
        // TODO: Implement refundPayment() method.
    }


    public function verifyPayment(Order $order)
    {
        $result = [];
        $payer = new stdClass();
        $payer->contact = new stdClass();
        $payer->contact->email = $order->user->username;
        $result['payer'] = $payer;

        $paymentParams = json_decode($order->gatewayResult);
        if (!$paymentParams) {
            $result['state'] = PaymentService::PAYMENT_STATE_NOPAID;
            return $result;
        }

        $payment = new ReturnedPayment($this->thepayConfig, (array) $paymentParams);
        if (!$payment->verifySignature()) {
            throw new ThepayException('Fraud payment data.');
        }

        $state = $payment->getStatus();
        switch ($state) {
            case self::STATUS_OK:
                $result['state'] = PaymentService::PAYMENT_STATE_PAID;
                break;
            case self::STATUS_CANCELED:
                $result['state'] = PaymentService::PAYMENT_STATE_CANCELED;
                break;
            case self::STATUS_NO_PAID:
            case self::STATUS_UNDERPAID:
            case self::STATUS_WAITING:
            case self::STATUS_CARD_DEPOSIT:
                $result['state'] = PaymentService::PAYMENT_STATE_NOPAID;
                break;
            case self::STATUS_ERROR:
            default:
                $result['state'] = PaymentService::PAYMENT_STATE_ERROR;
                break;
        }

        return $result;
    }


    private function buildUrl(Payment $payment): Url
    {
        $args = $payment->getArgs();
        $args['signature'] = $payment->getSignature();

        $url = new Url($this->thepayConfig->gateUrl);
        $url->setQuery($args);

        return $url;
    }


    private function convertPaymentMethod(string $goPayPaymentMethod): ?string
    {
        switch ($goPayPaymentMethod) {
            case PaymentInstrument::PAYMENT_CARD:
                return self::CHANNEL_CARD;
                break;

            case PaymentInstrument::BANK_ACCOUNT:
                return self::CHANNEL_WIRE_TRANSFER;
                break;

            default:
                throw new ThepayException('Unknown payment method');
        }
    }
}
