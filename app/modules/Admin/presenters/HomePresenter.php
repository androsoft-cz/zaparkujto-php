<?php

namespace App\Modules\Admin;

use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Modules\Admin\Components\HomepageStats\IHomepageStatsControlFactory;

class HomePresenter extends SecurePresenter
{
    /** @var IHomepageStatsControlFactory @inject */
    public $homepageStatsControlFactory;

    /** @var OrganizationsRepository @inject */
    public $organizationRepository;


    protected function beforeRender()
    {
        parent::beforeRender();
        /** @var Organization */
        $organization = $this->organizationRepository->findById($this->myUserData['org_id'])->fetch();
        $this->checkOrganizationDataAccess($organization);
        //$this->template->navbarTitle = $this->getTranslator()->translate('presenters.inspections.title', ['name' => $organization->name]);
        $this->template->navbarTitle = $organization->name;
    }


    protected function createComponentHomepageStatsControl(): Components\HomepageStats\HomepageStatsControl
    {
        $stats = $this->homepageStatsControlFactory->create();
        $stats->setUserData($this->myUserData);
        return $stats;
    }
}
