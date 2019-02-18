<?php

namespace App\Model\Facade;

use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;
use Nextras\Orm\Collection\ICollection;

final class OrganizationFacade
{

    /** @var OrganizationsRepository */
    private $organizationRepository;

    /**
     * @param OrganizationsRepository $organizationRepository
     */
    public function __construct(OrganizationsRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @return Organization[]|ICollection
     */
    public function getOrganizations()
    {
        return $this->organizationRepository->findAll();
    }

}
