<?php

namespace App\Modules\Base\Forms\Security;

use App\Core\Forms\BaseForm;

final class SignInForm extends BaseForm
{

    public function __construct(
        \Kdyby\Translation\Translator $translator
    )
    {
        $this->setTranslator($translator);

        $this->addGroup('');

        $this->addText('username', 'forms.signin.email')
            ->setRequired('forms.signin.rule.email');

        $this->addPassword('password', 'forms.signin.password')
            ->setRequired('forms.signin.rule.password');

        $this->addCheckbox('remember', 'forms.signin.remember');

        $this->addSubmit('submit', 'forms.signin.submit');
    }

}
