<?php

namespace App\Modules\Driver;

use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Exceptions\Runtime\InvalidReservationStateException;
use App\Model\Exceptions\Runtime\OrderException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Exceptions\Runtime\Reservation\TokenException;
use App\Model\Facade\ExtendReservationFacade;
use App\Model\Facade\OrderFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\ReservationTokens\ReservationToken;
use App\Model\Payment\PaymentService;
use App\Modules\Driver\Components\GoogleMap\DetailControl;
use App\Modules\Driver\Components\GoogleMap\IDetailControlFactory;
use App\Modules\Driver\Forms\Extend\ExtendReservationForm;
use App\Modules\Driver\Forms\Extend\IExtendReservationFormFactory;
use Markette\GopayInline\Api\Lists\PaymentInstrument;
use Markette\GopayInline\Exception\GopayException;
use Nette\Application\AbortException;
use Tracy\Debugger;

final class ExtendReservationPresenter extends BasePresenter
{

    /** @var ExtendReservationFacade @inject */
    public $extendReservationFacade;

    /** @var OrderFacade @inject */
    public $orderFacade;

    /** @var IExtendReservationFormFactory @inject */
    public $extendReservationFormFactory;

    /** @var IDetailControlFactory @inject */
    public $googleMapDetailControlFactory;

    /** @var PaymentService @inject */
    public $paymentService;

    /** @var ReservationToken */
    private $token;

    /** @var Place */
    private $place;

    /**
     * EMERGENCY EXTEND RESERVATION ********************************************
     */

    /**
     * @param string $token
     */
    public function actionExtend($token)
    {
        $this->token = $this->validateToken($token);
    }

    public function renderExtend()
    {
        $reservation = $this->token->reservation;
        $place = $reservation->place;

        // Fill form
        $this['extendForm']->setDefaults([
            'from' => $reservation->from,
            'to' => $reservation->to,
            'price' => $reservation->price,
            'extraprice' => $place->pricePerExtend,
        ]);

        // Fill template
        $this->template->place = $place;
    }

    /**
     * @return ExtendReservationForm
     */
    protected function createComponentExtendForm()
    {
        $form = $this->extendReservationFormFactory->create();

        $form->onSuccess[] = [$this, 'processExtendForm'];

        return $form;
    }

    /**
     * @param ExtendReservationForm $form
     */
    public function processExtendForm(ExtendReservationForm $form)
    {
        try {
            // Create extend reservation
            $extendedReservation = $this->extendReservationFacade->createExtendReservation($this->token->reservation, $form->values->extra);
        } catch (InvalidStateException $e) {
            Debugger::log($e);
            $this->flashMessage('Nepodařilo se neouzove prodloužit rezervaci.', 'danger');
            $this->redirect('this');

        } catch (InvalidReservationStateException $e) {
            Debugger::log($e);
            if ($e->state === Reservation::STATE_EXTENDED) {
                $this->flashMessage('Tuto rezervaci nelze nouzově prodloužít, ovlivnila by tím jinou nouzově prodlouženou rezervaci.', 'danger');
            } else {
                $this->flashMessage('Tuto rezervaci nelze nouzově prodloužit', 'danger');
            }

            $this->redirect('this');

        } catch (AbortException $e) {
            throw $e;

        } catch (\Exception $e) {
            $this->flashMessage('Nepodařilo se nouzově prodloužit rezervaci.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        }

        try {
            if ($form['extend1']->isSubmittedBy()) {
                $payment = PaymentInstrument::PAYMENT_CARD;
            } else if ($form['extend2']->isSubmittedBy()) {
                $payment = PaymentInstrument::BANK_ACCOUNT;
            } else {
                throw new InvalidStateException('Invalid payment instrument');
            }

            // Create gopay payment
            $result = $this->paymentService->createExtendPayment($extendedReservation, [
                'return_url' => $this->link('//ExtendReservation:extendProcess', ['id' => $extendedReservation->id]),
                'notify_url' => $this->link('//ExtendReservation:extendProcess', ['id' => $extendedReservation->id]),
                'payment' => $payment,
                'email' => $extendedReservation->order->user->username,
            ]);
            //todo: fix it!!!
            $this->orderFacade->updatePaymentProcess($extendedReservation->order, $result['paymentId'], Order::STATE_WAITING, $result['gw_url']);

            if ($this->isAjax()) {
                $this->payload->gopay = $result;
                $this->redrawControl('order');
            }
        } catch (PaymentException $e) {
            Debugger::log($e);
            $this->flashMessage('Vyskytl se problém na straně platební brány. Prosím opakujte akci znovu', 'danger');

        } catch (OrderException $e) {
            Debugger::log($e);
            $this->flashMessage('Vyskytl se problém při zpracování objednávky. Prosím opakujte akci znovu', 'danger');

            return;

        }
    }

    /**
     * Handle gopay notifications
     *
     * @param int $id Reservation ID
     */
    public function actionExtendProcess($id)
    {
        // Do canonization - for sure!
        $this->canonicalize();

        try {
            // Find reservation
            $reservation = $this->extendReservationFacade->getReservation($id);
            $this->place = $reservation->place;

            $verified = $this->orderFacade->verifyOrderPayment($reservation->order->id);
            $this->extendReservationFacade->applyPayment($reservation, $verified);

            $order = $verified->getOrder();

            if ($verified->isPaid()) {
                $this->setView('extend.success');
            } else if ($verified->isCanceled()) {
                $this->setView('extend.canceled');
            } else {
                $this->setView('extend.process');
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
        } catch (\Exception $e) {
            $this->flashMessage('Vyskytl se neznámý problém. Kontaktujte správce.', 'danger');
            Debugger::log($e);
            $this->redirect(':Driver:Home:default');
        }
    }

    /**
     * SPARE RESERVATION *******************************************************
     */

    /**
     * @param string $token
     */
    public function actionSpare($token)
    {
        $this->token = $this->validateToken($token);
        $this->place = $this->token->reservation->place;
    }

    public function renderSpare()
    {
        $this->template->token = $this->token;
        $this->template->order = $this->token->reservation->order;
    }

    /**
     * @param string $token
     */
    public function handleAcceptSpare($token)
    {
        $this->token = $this->validateToken($token);

        try {
            $this->extendReservationFacade->acceptSpareReservation($this->token->reservation);
            $this->flashMessage('Náhradní místo přijato.', 'success');
            $this->redirect(':Driver:Home:default');
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            Debugger::log($e);
            $this->flashMessage('Nepodařilo se přijmout náhradní místo, prosím opakujte akci znovu.');
        }
    }

    /**
     * @param string $token
     */
    public function handleRejectSpare($token)
    {
        $this->token = $this->validateToken($token);
        try {
            $this->extendReservationFacade->rejectSpareReservation($this->token->reservation);
            $this->flashMessage('Náhradní místo odmítnuto. Omlouváme se a doufáme, že najdete lepší.', 'success');
            $this->redirect(':Driver:Home:default');
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            Debugger::log($e);
            $this->flashMessage('Nepodařilo se přijmout náhradní místo, prosím opakujte akci znovu.', 'danger');
        }
    }

    /**
     * COMPONENTS **************************************************************
     */

    /**
     * @return DetailControl
     */
    protected function createComponentGoogleMapDetail()
    {
        return $this->googleMapDetailControlFactory->create($this->place);
    }

    /**
     * HELPERS *****************************************************************
     */

    /**
     * @param string $token
     * @return ReservationToken
     */
    protected function validateToken($token)
    {
        try {
            $token = $this->extendReservationFacade->validateToken($token);
        } catch (TokenException $e) {
            switch ($e->getCode()) {
                case TokenException::NOT_FOUND:
                    $this->flashMessage('Bohužel takový token neexistuje');
                    break;
                case TokenException::EXPIRED:
                    $this->flashMessage('Bohužel token už není platný');
                    break;
                case TokenException::APPLIED:
                    $this->flashMessage('Bohužel token už byl uplatněn');
                    break;
            }

            $this->redirect(':Driver:Home:default');
        }

        return $token;
    }
}
