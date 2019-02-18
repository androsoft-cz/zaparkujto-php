<?php

namespace App\Modules\Driver;

use App\Model\Exceptions\Runtime\GopayException;
use App\Model\Exceptions\Runtime\OrderException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Facade\OrderFacade;
use App\Model\Mailing\MailService;
use App\Model\Orm\Model;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Payment\PaymentService;
use App\Model\Payment\ThePayService;
use App\Modules\Driver\Components\GoogleMap\DetailControl;
use App\Modules\Driver\Components\GoogleMap\IDetailControlFactory;
use Nette\Application\AbortException;
use Tracy\Debugger;

class OrderPresenter extends BasePresenter
{
    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var IDetailControlFactory @inject */
    public $googleMapDetailControlFactory;

    /** @var MailService @inject */
    public $mailService;

    /** @var Model @inject */
    public $model;

    /** @var Order */
    private $order;

    /** @var PaymentService @inject */
    public $paymentService;


    //todo: delete this in future, only for back compatibility for unpaid gopay orders with stored old backlinks
    public function actionProcess($orderId)
    {
        $this->redirect('processGoPay', ['orderId' => $orderId]);
    }


    public function actionProcessGoPay($orderId)
    {
        // Do canonization - for sure!
        $this->canonicalize();

        $gopayResponse = (object) $_GET;

        try {
            $verified = $this->orderFacade->verifyOrderPayment($orderId);
            $paymentCardNumber = $verified->getResponse()->data['payer']->payment_card->card_number ?? NULL;
            $order = $this->orderFacade->updatePaymentProcess($verified->getOrder(), $gopayResponse->id, $gopayResponse, $paymentCardNumber);

            if ($verified->isPaid()) {
                $this->mailService->sendCustomerOrderPaid($order);
            }

            foreach ($order->reservations as $reservation) {
                $reservationId = $reservation->id;
                break;
            }

            if ($verified->isPaid()) {
                $this->flashMessage('driver.msgReservationSucceed', 'success');
                $this->redirect(':Driver:Reservation:detail', $reservationId);
            } else {
                if ($verified->isCanceled()) {
                    $this->flashMessage('driver.msgReservationCanceled', 'danger');
                    $this->redirect(':Driver:Reservation:detail', $reservationId);
                } else {
                    $this->flashMessage('driver.msgReservationInProgress', 'info');
                    $this->redirect(':Driver:Reservation:detail', $reservationId);
                }
            }

            // Fill template
            $this->template->order = $order;
        } catch (GopayException $e) {
            $this->flashMessage('Vyskytl se problém na straně platební brány. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (OrderException $e) {
            $this->flashMessage('Vyskytl se problém během ověřování platby. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Vyskytl se neznámý problém. Kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        }
    }


    public function actionProcessCsobPay()
    {
        // Do canonization - for sure!
        $this->canonicalize();

        $csobResponse = (object) $_POST;

        try {
            $orderId = base64_decode($csobResponse->merchantData);
            $verified = $this->orderFacade->verifyOrderPayment($orderId);
            $paymentCardNumber = $verified->getResponse()['payer']->payment_card->card_number ?? NULL;
            $order = $this->orderFacade->updatePaymentProcess($verified->getOrder(), $csobResponse->payId, $csobResponse, $paymentCardNumber);

            if ($verified->isPaid()) {
                $this->mailService->sendCustomerOrderPaid($order);
            }

            foreach ($order->reservations as $reservation) {
                $reservationId = $reservation->id;
                break;
            }

            if ($verified->isPaid()) {
                $this->flashMessage('driver.msgReservationSucceed', 'success');
                $this->redirect(':Driver:Reservation:detail', $reservationId);
            } else {
                if ($verified->isCanceled()) {
                    $this->flashMessage('driver.msgReservationCanceled', 'danger');
                    $this->redirect(':Driver:Reservation:detail', $reservationId);
                } else {
                    $this->flashMessage('driver.msgReservationInProgress', 'info');
                    $this->redirect(':Driver:Reservation:detail', $reservationId);
                }
            }

            // Fill template
            $this->template->order = $order;
        } catch (GopayException $e) {
            $this->flashMessage('Vyskytl se problém na straně platební brány. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (OrderException $e) {
            $this->flashMessage('Vyskytl se problém během ověřování platby. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Vyskytl se neznámý problém. Kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        }
    }


    public function actionProcessThePay()
    {
        try {
            $thepayResponse = (object) $_GET;
            /** @var Order $order */
            $order = $this->model->orders->getById($thepayResponse->orderId);
            if (!$order) {
                throw new OrderException('Order not found.');
            }

            $order = $this->orderFacade->updatePaymentProcess($order, $thepayResponse->paymentId, $thepayResponse);
            $verified = $this->orderFacade->verifyOrderPayment($order->id);
            $order = $verified->getOrder();

            switch ($thepayResponse->status) {
                case ThePayService::STATUS_OK:
                case ThePayService::STATUS_CARD_DEPOSIT:
                    $this->mailService->sendCustomerOrderPaid($order);
                    $this->flashMessage('driver.msgReservationSucceed', 'success');
                    break;

                case ThePayService::STATUS_CANCELED:
                    $this->flashMessage('driver.msgReservationCanceled', 'danger');
                    break;

                case ThePayService::STATUS_ERROR:
                    $this->flashMessage('driver.msgReservationFailed', 'danger');
                    break;

                case ThePayService::STATUS_WAITING:
                case ThePayService::STATUS_UNDERPAID:
                default:
                    $this->flashMessage('driver.msgReservationInProgress');
                    break;
            }

            /** @var Reservation $reservation */
            $reservation = $order->reservations->get()->fetch();
            $this->redirect(':Driver:Reservation:detail', $reservation->id);
        } catch (PaymentException $e) {
            $this->flashMessage('Vyskytl se problém na straně platební brány. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (OrderException $e) {
            $this->flashMessage('Vyskytl se problém během ověřování platby. Prosím kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->flashMessage('Vyskytl se neznámý problém. Kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        }
    }


    /**
     * NOTIFY ******************************************************************
     */

    public function actionNotifyGoPay($orderId)
    {
        $verified = $this->orderFacade->verifyOrderPayment($orderId);
        if ($verified->isPaid()) {
            $this->mailService->sendCustomerOrderPaid($verified->getOrder());
        }

        $this->terminate();
    }

    /**
     * COMPONENTS **************************************************************
     */

    /**
     * @return DetailControl
     */
    protected function createComponentGoogleMapDetail()
    {
        return $this->googleMapDetailControlFactory->create($this->order->reservations->get()->fetch()->place);
    }
}
