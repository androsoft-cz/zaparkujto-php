<?php

namespace App\Model\Orm\Orders;

use App\Model\Payment\PaymentService;
use Markette\GopayInline\Http\Response;

final class VerifiedOrder
{
    /** @var Order */
    private $order;

    private $response;


    /**
     * VerifiedOrder constructor.
     *
     * @param Order    $order
     * @param Response $response
     */
    public function __construct(Order $order, $response)
    {
        $this->order = $order;
        $this->response = $response;
    }


    public function getOrder(): Order
    {
        return $this->order;
    }


    public function isPaid(): bool
    {
        return $this->response['state'] === PaymentService::PAYMENT_STATE_PAID;
    }


    public function isCanceled(): bool
    {
        return $this->response['state'] === PaymentService::PAYMENT_STATE_CANCELED;
    }


    /**
     * @return array|Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
