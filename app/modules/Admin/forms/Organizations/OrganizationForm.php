<?php

namespace App\Modules\Admin\Forms\Organizations;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use Nette\Forms\Form;
use Nette\Forms\IControl;

final class OrganizationForm extends BaseForm
{

    /** @var OrganizationsRepository $organizationRepository */
    private $organizationsRepository;

    public function __construct(
        \Kdyby\Translation\Translator $translator,
        OrganizationsRepository $organizationsRepository
    )
    {
        parent::__construct();
        $this->organizationsRepository = $organizationsRepository;

        $this->setTranslator($translator);

        $this->addGroup('');
        $this->addText('name', 'forms.organization.name')
            ->setRequired('forms.organization.rule.name');

        $this->addText('slug', 'common.slug')
            ->addRule(Form::PATTERN, 'admin.msgWrongSlugFormat', '[a-z0-9-]+')
            ->addRule([$this, 'slugValidation'], 'admin.msgDuplicateSlug')
            ->setRequired();

        $this->addText('state', 'forms.organization.state')
            ->setRequired('forms.organization.rule.state');

        $this->addText('identificationNumber', 'forms.organization.cin');

        $this->addText('email', 'forms.organization.email');

        $this->addText('telephone', 'forms.organization.phone');

        $this->addText('address1', 'forms.organization.street');

        $this->addText('address2', 'forms.organization.city');

        $this->addText('lat', 'common.lat');

        $this->addText('lng', 'common.lng');

        $this->addText('usersCreditGain', 'forms.organization.credits');

        $this->addText('minIntervalBetweenReservations', 'forms.organization.interval');

        $this->addCheckbox('demo', 'forms.organization.demo');

        $this->addText('smsKeyword', 'forms.organization.smsKeyword');

        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.organization.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }

    public function processForm(OrganizationForm $form)
    {
        $values = $form->getValues();
        if ($values->id != '') {
            /** @var Organization $organization */
            $organization = $this->organizationsRepository->getById($values->id);

            /** @var Contact $contact */
            $contact = $organization->contact;
        } else {
            /** @var Contact $contact */
            $contact = new Contact();

            /** @var Organization $organization */
            $organization = new Organization();
            $organization->contact = $contact;
        }

        $organization->name = $values->name;
        $organization->slug = $values->slug;
        $organization->lat = $values->lat;
        $organization->lng = $values->lng;
        $organization->minIntervalBetweenReservations = $values->minIntervalBetweenReservations;
        $organization->usersCreditGain = $values->usersCreditGain;
        $organization->demo = $values->demo ? 1 : 0;
        $organization->smsKeyword = $values->smsKeyword;

        $contact->state = $values->state;
        $contact->address1 = $values->address1;
        $contact->address2 = $values->address2;
        $contact->telephone = $values->telephone;
        $contact->email = $values->email;
        $contact->identificationNumber = $values->identificationNumber;

        $this->organizationsRepository->persistAndFlush($organization);
    }


    public function setDefaultEntity(Organization $organization)
    {
        $this->setDefaults($organization->toArray());

        $contactDefaults = $organization->contact->toArray();
        unset($contactDefaults['id']);
        $this->setDefaults($contactDefaults);
    }


    /**
     * @param IControl $control
     * @return bool
     */
    public function slugValidation(IControl $control)
    {
        /** @var Organization $organization */
        $organization = $this->organizationsRepository->getBySlug($control->getValue());

        return !$organization || $organization->id == $this->values->id;
    }
}
