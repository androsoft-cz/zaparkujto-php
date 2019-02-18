<?php

namespace App\Modules\Base\Forms\Security;

use App\Core\Forms\BaseForm;

final class SignUpForm extends BaseForm
{

    public function __construct(
        \Kdyby\Translation\Translator $translator
    )
    {
        $this->setTranslator($translator);

        $this->addGroup('');

        $this->addText('username', 'forms.signup.email')
            ->addRule(self::EMAIL)
            ->setRequired('forms.signup.rule.email');

        $this->addText('name', 'forms.signup.email');

        $password1 = $this->addPassword('password', 'forms.signup.password')
            ->setRequired('forms.signup.rule.password');

        $password2 = $this->addPassword('password', 'forms.signup.passwordAgain')
            ->addConditionOn($password1, self::FILLED)
            ->addRule(self::FILLED, 'forms.signup.rule.passwordAgain');

        $password2->addCondition(self::FILLED)->addRule(self::EQUAL, 'forms.signup.rule.notMatch', $password1);

        $this->addCheckbox('consent', 'forms.signup.consent')
            ->addRule(
                self::NOT_EQUAL,
                'forms.signup.rule.consent'
            );

        $this->addSubmit('submit', 'forms.signup.submit');
    }

}
