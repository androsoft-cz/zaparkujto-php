<?php

namespace App\Model\Mailing;

use App\Model\Exceptions\Runtime\MailException;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Users\User;
use App\Model\Pdf\PdfFactory;
use Exception;
use Nette\Mail\Message;

final class MailService
{

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    /** @var MailFactory */
    private $factory;

    /** @var ReceiptTemplateFactory */
    private $receiptTemplateFactory;

    /** @var PdfFactory */
    private $pdfFactory;


    /**
     * @param \Kdyby\Translation\Translator
     * @param MailFactory $factory
     * @param ReceiptTemplateFactory $receiptTemplateFactory
     * @param PdfFactory $pdfFactory
     */
    public function __construct(
        \Kdyby\Translation\Translator $translator,
        MailFactory $factory,
        ReceiptTemplateFactory $receiptTemplateFactory,
        PdfFactory $pdfFactory
    )
    {
        $this->translator = $translator;
        $this->factory = $factory;
        $this->receiptTemplateFactory = $receiptTemplateFactory;
        $this->pdfFactory = $pdfFactory;
    }

    /**
     * @param Message $message
     * @throws MailException
     */
    protected function send(Message $message)
    {
        try {
            $this->factory->send($message);
        } catch (Exception $e) {
            throw new MailException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param Order $order
     * @return void
     * @throws MailException|Exception
     */
    public function sendCustomerOrderPaid(Order $order)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.ordersummary', ['id' => $order->id]));
        $message->addTo($order->email === NULL ? $order->user->username : $order->email);

        $template = $this->factory->createTemplate('order/summary');
        $template->order = $order;
        $template->mail = $message;
        $message->setHtmlBody($template);

        $receiptTemplate = $this->receiptTemplateFactory->create($order);
        $pdf = $this->pdfFactory->create($receiptTemplate, PdfFactory::PAGE_FORMAT_A4_L);
        $pdfTempFile = $this->pdfFactory->savePdf($pdf, $this->translator->translate('mail.billParking', ['vs' => $order->vs]));
        $message->addAttachment($pdfTempFile);

        try {
            $this->send($message);
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            unset($pdfTempFile);
        }
    }

    /**
     * @param Reservation $reservation
     * @param bool $canExtend
     * @return void
     * @throws MailException
     */
    public function sendCustomerReservationSoonExpire(Reservation $reservation, $canExtend = FALSE)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.soonExpire', ['id' => $reservation->id]));
        $message->addTo($reservation->order->user->username);

        $template = $this->factory->createTemplate('reservation/soonExpire');
        $template->reservation = $reservation;
        $template->canExtend = $canExtend;
        $template->mail = $message;

        $message->setHtmlBody($template);

        $this->send($message);
    }

    /**
     * @param Reservation $reservation
     * @return void
     * @throws MailException
     */
    public function sendCustomerReservationCancelledAndRefund(Reservation $reservation)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.soonExpire', ['id' => $reservation->id]));
        $message->addTo($reservation->order->user->username);

        $template = $this->factory->createTemplate('reservation/cancelledRefund');
        $template->reservation = $reservation;
        $template->mail = $message;
        $message->setHtmlBody($template);

        $this->send($message);
    }

    /**
     * @param Reservation $reservation1
     * @param Reservation $reservation2
     * @return void
     * @throws MailException
     */
    public function sendCustomerReservationCancelledAndOfferSparePlace(Reservation $reservation1, Reservation $reservation2)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.soonExpire', ['id' => $reservation1->id]));
        $message->addTo($reservation1->order->user->username);

        $template = $this->factory->createTemplate('reservation/cancelledOfferSparePlace');
        $template->reservation1 = $reservation1;
        $template->reservation2 = $reservation2;
        $template->mail = $message;
        $message->setHtmlBody($template);

        $this->send($message);
    }

    /**
     * @param Reservation $reservation1
     * @param Reservation $reservation2
     * @return void
     * @throws MailException
     */
    public function sendCustomerAcceptSpareReservation(Reservation $reservation)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.soonExpire', ['id' => $reservation->id]));
        $message->addTo($reservation->order->user->username);

        $template = $this->factory->createTemplate('reservation/acceptSparePlace');
        $template->reservation = $reservation;
        $template->mail = $message;
        $message->setHtmlBody($template);

        $this->send($message);
    }

    /**
     * @param string $token
     * @param User $user
     * @param string $locale
     * @throws MailException
     */
    public function sendPasswordReset($token, User $user, $locale)
    {
        $message = $this->factory->createMessage();
        $message->setSubject($this->translator->translate('mail.resetPassword.passwordChangeRequest', NULL, [], NULL, $locale));
        $message->addTo($user->username);

        $template = $this->factory->createTemplate('password/reset');
        $template->token = $token;
        $template->mail = $message;
        $template->locale = $locale;

        $message->setHtmlBody($template);

        $this->send($message);
    }

}
