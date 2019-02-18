<?php

namespace App\Modules\Admin;

use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Modules\Admin\Forms\Organizations\IBankDetailFormFactory;

class BankDetailPresenter extends SecurePresenter
{
    /** @var IBankDetailFormFactory @inject */
    public $formFactory;

    /** @var OrganizationsRepository @inject */
    public $organizationsRepository;


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.bankdetail.title');
    }


    public function actionDetail($id = NULL)
    {
        if (!isset($id)) { //pokud není id zvoleno, bude pouzito id vlastní organizace
            $id = $this->myUserData['org_id'];
        }

        /** @var Organization $organization */
        $organization = $this->organizationsRepository->getById($id);

        $this->checkOrganizationDataAccess($organization);

        $this['form']->setDefaults([
            'id' => $id,
            'paymentGateway' => $organization->paymentGateway->id ?? NULL,
            'vsPrefix' => $organization->vsPrefix,
        ]);

        // only root can change gateway
        $this['form']['paymentGateway']->setDisabled(!$this->user->isInRole('root'));
    }


    public function createComponentForm()
    {
        $form = $this->formFactory->create();
        $form->onSuccess[] = [$this, 'onSuccess'];

        return $form;
    }


    public function onSuccess($form, $values)
    {
        /** @var Organization $organization */
        $organization = $this->organizationsRepository->getById($values->id);

        $organization->paymentGateway = $values->paymentGateway;
        $organization->vsPrefix = $values->vsPrefix;
        $this->organizationsRepository->persistAndFlush($organization);
        $this->flashMessage('flashmessages.recordSaved', 'success');
        $this->redirect('detail');
    }
}
