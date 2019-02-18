<?php

namespace App\Modules\Admin;

use App\Model\Manager\OrgManager;
use Markette\Gopay;

class ContactsPresenter extends SecurePresenter
{

    public function createComponentForm()
    {
        $form = new \Nette\Application\UI\Form();

        $form->addGroup('');
        $form->addText('contacts', 'presenters.contacts.form.contacts');

        $form->addHidden('id');
        $form->addSubmit('submit', 'presenters.contacts.form.submit');

        $form->onSuccess[] = [$this, 'onSuccess'];
        $form->setDefaults($this->row);

        return $form;
    }

}
