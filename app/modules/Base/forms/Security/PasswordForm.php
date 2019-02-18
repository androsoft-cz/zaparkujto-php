<?php

namespace App\Modules\Base\Forms\Security;

use App\Core\Forms\BaseForm;

final class PasswordForm extends BaseForm
{

    /**
     * @param int
     */
    public function __construct(
        $userId,
        \Kdyby\Translation\Translator $translator
    )
    {
        $this->setTranslator($translator);

        $this->addGroup('');

        $this->addHidden('id')->setValue($userId);

        $password1 = $this->addPassword('katty', 'forms.password.newPassword')
            ->setRequired('forms.password.rule.requred');

        $password2 = $this->addPassword('kitty', 'forms.password.newPasswordAgain')
            ->addConditionOn($password1, self::FILLED)
            ->addRule(self::FILLED, 'forms.password.rule.again');

        $password2->addCondition(self::FILLED)
            ->addRule(self::EQUAL, 'forms.password.rule.notMatch', $password1);

        $this->addSubmit('change', 'forms.password.change')
            ->setAttribute('class', 'btn-sm btn-success pull-right');
    }

}
