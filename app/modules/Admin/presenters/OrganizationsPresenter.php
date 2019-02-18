<?php

namespace App\Modules\Admin;

use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Modules\Admin\Forms\Organizations\IOrganizationFormFactory;
use App\Modules\Admin\Forms\Organizations\OrganizationForm;
use App\Modules\Admin\Grids\Organizations\IOrganizationsGridFactory;
use Nette\Application\UI\Form;

class OrganizationsPresenter extends SecurePresenter
{
    /** @var IOrganizationsGridFactory @inject */
    public $gridFactory;

    /** @var IOrganizationFormFactory @inject */
    public $formFactory;

    public $longTextField;

    /** @var OrganizationsRepository @inject */
    public $organizationsRepository;


    public function startup()
    {
        parent::startup();
        $organization = $this->organizationsRepository->findById($this->myUserData['org_id'])->fetch();
        $this->checkOrganizationDataAccess($organization);
    }


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.organizations.title');
    }


    public function createComponentGrid()
    {
        return $this->gridFactory->create();
    }


    public function actionDelete($id)
    {
        $this->organizationsRepository->delete($id);
        $this->redirect('default');
    }


    public function actionDetail($id = NULL)
    {
        if (!isset($id)) { //pokud není id zvoleno, bude pouzito id vlastní organizace
            //až bude hotovo přihlašování
            //$id = $this->user->getIdentity()->organizationId;
            $id = $this->myUserData['org_id'];
        }

        /** @var Organization $organization */
        $organization = $this->organizationsRepository->getById($id);

        /** @var OrganizationForm $form */
        $form = $this->getComponent('form');
        $form->setDefaultEntity($organization);
    }


    /**
     * @return OrganizationForm
     */
    public function createComponentForm()
    {
        $form = $this->formFactory->create();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('detail');
        };

        return $form;
    }


    /***
     * Funkce pro zmeni vlastni organizace, funguje pouze pro root uzivatele
     *
     * @param $id
     */
    public function actionSetMyOrg($id)
    {
        $this->rootedOrgId = $id; //root má možnost zasahovat i do jiných než vlastních organizaci, díky persistentnímu parametru mu zmenime jeho organizaci
        $this->redirect('detail');
    }


    /**
     * Akce pro úpravu dlouhých textů (obchodní podmínky, souhlas se zpracováním obchodních údajů)
     *
     * @param $field
     */
    public function actionEditLongText($field)
    {
        switch ($field) {
            case 'terms_and_conditions':
                $longTextField = $field;
                $this->template->longTextCaption = $this->getTranslator()->translate('presenters.organizations.terms');
                break;
            case 'consent_processing_personal_data':
                $longTextField = $field;
                $this->template->longTextCaption = $this->getTranslator()->translate('presenters.organizations.termsPersonal');
                break;
            case 'contacts':
                $longTextField = 'contacts';
                $this->template->longTextCaption = $this->getTranslator()->translate('presenters.organizations.termsContacts');
                break;
            default:
                $longTextField = 'undefined';
        }

        $organization = $this->organizationsRepository->getById($this->myUserData['org_id']);
        $longTextForm = $this->getComponent('longTextForm');
        $longTextForm->addTextArea($longTextField);
        $longTextForm->setDefaults($organization->toArray());

        $this->template->longTextField = $longTextField;
    }


    /**
     * Vytvoří formulář dlouhého textu
     *
     * @return Form
     */
    public function createComponentLongTextForm()
    {
        $form = new Form();
        $form->setTranslator($this->getTranslator());
        $form->addSubmit('submit', 'presenters.organizations.form.submit');
        $form->onSuccess[] = [$this, 'editLongTextSuccess'];

        return $form;
    }


    /**
     * Zpracování formuláře dlouhého textu
     *
     * @param $form
     * @param $values
     */
    public function editLongTextSuccess($form, $values) // @codingStandardsIgnoreLine
    {
        $this->orgManager->updateLongText($this->longTextField, $values[$this->longTextField]);
        $this->redirect(':Admin:Org:detail');
    }
}
