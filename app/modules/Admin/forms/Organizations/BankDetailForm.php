<?php

namespace App\Modules\Admin\Forms\Organizations;

use App\Core\Forms\BaseForm;
use App\Model\Orm\PaymentGateways\PaymentGatewaysRepository;
use Kdyby\Translation\Translator;
use Nette\Forms\Form;

final class BankDetailForm extends BaseForm
{
    public function __construct(
        Translator $translator,
        PaymentGatewaysRepository $paymentGatewaysRepository
    ) {
        parent::__construct();

        $this->setTranslator($translator);

        $this->addGroup('');
        $this->addHidden('id');

        $this->addSelect('paymentGateway', 'forms.bankdetail.paymentGateway', $paymentGatewaysRepository->findAll()->fetchPairs('id', 'description'))
            ->setRequired(TRUE);

        $this->addText('vsPrefix', 'forms.bankdetail.vs')
            ->setRequired(TRUE)
            ->addRule(Form::MAX_LENGTH, NULL, 2);

        $this->addSubmit('submit', 'forms.bankdetail.submit');
    }
}
