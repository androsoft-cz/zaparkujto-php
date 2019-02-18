<?php

namespace App\Model;

use App\Model\Exceptions\RuntimeException;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Organizations\OrganizationsRepository;

final class OrganizationRequest
{

    /** @var string */
    private $oid;

    /** @var Organization */
    private $organization;

    /** @var OrganizationsRepository */
    private $organizationRepository;

    /**
     * @param OrganizationsRepository $organizationsRepository
     */
    public function __construct(OrganizationsRepository $organizationsRepository)
    {
        $this->organizationRepository = $organizationsRepository;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        if (!$this->organization && !$this->oid) {
            //throw new RuntimeException('No organization set');
            return NULL;
        }

        if (!$this->organization) {
            $this->organization = $this->organizationRepository->getById($this->oid);
            if (!$this->organization) {
                throw new RuntimeException('Organization not found');
            }
        }

        return $this->organization;
    }

    /**
     * @param string $id
     */
    public function setOrganization($id)
    {
        $this->oid = $id;
        $this->organization = NULL;
    }

}
