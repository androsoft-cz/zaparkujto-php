<?php

namespace App\Model\Mailing;

use App\Model\Facade\VatFacade;
use App\Model\Orm\Orders\Order;
use Nette\Application\UI\ITemplate;

class ReceiptTemplateFactory
{

    /** @var MailFactory */
    private $mailFactory;

    /** @var VatFacade */
    private $vatFacade;


    /**
     * @param MailFactory $factory
     * @param VatFacade $vatFacade
     */
    public function __construct(MailFactory $factory, VatFacade $vatFacade)
    {
        $this->mailFactory = $factory;
        $this->vatFacade = $vatFacade;
    }


    /**
     * @param Order $order
     * @return ITemplate
     */
    public function create(Order $order)
    {
        $template = $this->mailFactory->createTemplate('receipt/receipt');

        $template->vs = $order->vs;

        $reservation = NULL;

        foreach ($order->reservations as $_reservation) {
            $reservation = $_reservation;
            break;
        }

        $template->from = $reservation->from;
        $template->duration = strtotime($reservation->to) - strtotime($reservation->from);

        $place = $reservation->place;
        $organization = $place->organization;
        $organizationContact = $organization->contact;

        $template->supplierName = $this->formatString($organizationContact->company);
        $template->supplierStreet = $this->formatString($organizationContact->address1);
        $template->supplierCity = $this->formatString($organizationContact->address2);
        $template->supplierIC = $this->formatString($organizationContact->identificationNumber);
        $template->supplierDIC = $this->formatString($organizationContact->taxIdentificationNumber);
        $template->supplierPhone = $this->formatString($organizationContact->telephone);
        $template->supplierEmail = $this->formatString($organizationContact->email);

        $template->city = $organization->name;
        $template->street = $place->streetName;

        $template->untaxedPrice = $this->vatFacade->getUntaxedPrice($reservation->price);
        $template->vat = $this->vatFacade->getVat($reservation->price);
        $template->vatPercentage = $this->vatFacade->getVatPercentage();
        $template->taxedPrice = $reservation->price;

        if ($order->user) {
            $user = $order->user;
            $contact = $user->contact;

            $template->hasUser = TRUE;
            $template->userName = strlen($user->name) ? $user->name : $this->formatString($contact->company);
            $template->userStreet = $this->formatString($contact->address1);
            $template->userCity = $this->formatString($contact->address2);
            $template->userIC = $this->formatString($contact->identificationNumber);
            $template->userDIC = $this->formatString($contact->taxIdentificationNumber);
        } else {
            $template->hasUser = FALSE;
        }

        return $template;
    }


    /**
     * @param string $s
     * @return string|null
     */
    private function formatString($s)
    {
        return strlen($s) ? $s : NULL;
    }

}
