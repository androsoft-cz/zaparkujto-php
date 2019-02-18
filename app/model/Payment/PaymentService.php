<?php

namespace App\Model\Payment;

use App\Model\Exceptions\Runtime\OrderException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Exceptions\RuntimeException;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Model;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\PaymentGateways\PaymentGateway;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Users\User;
use Kdyby\Translation\Translator;
use Markette\GopayInline\Api\Lists\PaymentInstrument;
use Markette\GopayInline\Api\Lists\SwiftCode;
use Markette\GopayInline\Exception\HttpException;
use Nette\Application\LinkGenerator;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Tracy\Debugger;

final class PaymentService
{
    use SmartObject;
    const PAYMENT_STATE_NOPAID = 1;
    const PAYMENT_STATE_PAID = 2;
    const PAYMENT_STATE_CANCELED = 3;
    const PAYMENT_STATE_ERROR = 4;
    const PAYMENT_STATE_REFUNDED = 5;

    /** @var Translator */
    private $translator;

    /** @var Model */
    private $model;

    /** @var LinkGenerator */
    private $linkGenerator;


    public function __construct(Translator $translator, Model $model, LinkGenerator $linkGenerator)
    {
        $this->translator = $translator;
        $this->model = $model;
        $this->linkGenerator = $linkGenerator;
    }


    public function createPayment(Organization $organization, Order $order, string $paymentType): ?array
    {

        $paymentGateway = $this->getPaymentGateway($organization);
        $items = [];
        foreach ($order->reservations as $reservation) {
            $items[] = [
                'name' => $this->translator->translate('misc.order.place', ['name' => $reservation->place->identifier]),
                'amount' => $reservation->price,
            ];
        }

        // Prepare payment
        $payment = [
            'amount' => $order->price,
            'currency' => 'CZK',
            'order_number' => $order->vs,
            'order_description' => $this->translator->translate('misc.order.placeDesc'),
            'items' => $items,
            'lang' => 'cs',
            'payer' => [
                'contact' => [
                    'email' => $order->user->username,
                ],
            ],
        ];

        // Choose payment instrument
        switch ($paymentType) {
            case PaymentInstrument::PAYMENT_CARD:
                $payment['payer']['default_payment_instrument'] = PaymentInstrument::PAYMENT_CARD;
                $payment['payer']['allowed_payment_instruments'] = [PaymentInstrument::PAYMENT_CARD];
                break;

            case PaymentInstrument::BANK_ACCOUNT:
                $payment['payer']['default_payment_instrument'] = PaymentInstrument::BANK_ACCOUNT;
                $payment['payer']['allowed_payment_instruments'] = [PaymentInstrument::BANK_ACCOUNT];
                $payment['payer']['allowed_swifts'] = SwiftCode::cz();
                break;

            case PaymentInstrument::PRSMS:
                $payment['payer']['default_payment_instrument'] = PaymentInstrument::PRSMS;
                $payment['payer']['allowed_payment_instruments'] = [PaymentInstrument::PRSMS];
                break;

            default:
                throw new OrderException('Invalid payment instrument');
        }

        if ($paymentGateway instanceof CsobService) {
            $payment['short_description'] = $this->translator->translate('misc.order.shortDesc');
        }

        try {
            if ($order->authorizationPayment) {
                $response = $paymentGateway->createAuthorizationPayment($payment, $order->id);
            } elseif ($order->paymentCard) {
                $response = $paymentGateway->createRecurringPayment($payment, $order->id, $order->paymentCard);
            } else {
                $response = $paymentGateway->createSimplePayment($payment, $order->id);
            }

            return [
                'gw_url' => $response['gw_url'],
                'paymentId' => $response['id'],
                'payeeId' => $organization->id,
            ];
        } catch (HttpException $e) {
            Debugger::log($e);
            throw new PaymentException('Failed during processing payment.', 0, $e);
        }
    }


    public function verifyPayment(Order $order)
    {
        $organization = $this->model->organizations->getById($order->payeeId);
        if (!$organization) {
            throw new PaymentException('Can not get payee data');
        }

        $paymentGateway = $this->getPaymentGateway($organization);
        try {
            return $paymentGateway->verifyPayment($order);
        } catch (HttpException $e) {
            Debugger::log($e);
            throw new PaymentException('Failed during verifying payment', 0, $e);
        }
    }


    public function createExtendPayment(Reservation $reservation, array $parameters): ?array
    {
        // TODO: Implement createExtendPayment() method.
        return NULL;
    }


    public function refundPayment(Reservation $reservation, float $price): void
    {
        // TODO: Implement refundPayment() method.
    }


    public function payByCredit(Order $order, User $user): ?Credit
    {
        try {
            //todo: Move to CreaditFacade as new method
            $credit = new Credit();
            $credit->user = $user;
            $credit->createdAt = new DateTime();
            $credit->order = $order;
            $credit->price = -($order->price);
            $credit->movementType = Credit::MOVEMENT_TYPE_COSTS_PARKING;
            $this->model->persistAndFlush($credit);

            return $credit;
        } catch (RuntimeException $e) {
            throw new PaymentException('Failed during credits pay.', $e->getCode(), $e->getPrevious());
        }
    }


    private function getPaymentGateway(Organization $organization): IPaymentGatewayService
    {
        try {
            if (!$organization->paymentGateway || $organization->paymentGateway->deletedAt !== NULL) {
                throw new PaymentException('Invalid payment gateway settings.');
            }

            $paymentSettings = json_decode($organization->paymentGateway->setting);
            $paymentSettings->testMode = $organization->paymentGateway->testMode;
            switch ($organization->paymentGateway->gateway) {
                case PaymentGateway::GATEWAY_GOPAY:
                    return new GopayService($paymentSettings, $this->linkGenerator);
                    break;

                case PaymentGateway::GATEWAY_THEPAY:
                    return new ThePayService($paymentSettings, $this->linkGenerator);
                    break;

                case PaymentGateway::GATEWAY_CSOB:
                    return new CsobService($paymentSettings, $this->linkGenerator);

                default:
                    throw new PaymentException('Unknown payment gateway.');
            }
        } catch (RuntimeException $exception) {
            throw new PaymentException('Invalid payment gateway configuration.');
        }
    }
}
