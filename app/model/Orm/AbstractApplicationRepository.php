<?php

namespace App\Model\Orm;

use App\Model\OrganizationRequest;
use Nextras\Orm\Collection\ICollection;

abstract class AbstractApplicationRepository extends AbstractRepository
{

    /** @var OrganizationRequest */
    protected $requestOrganization;

    /**
     * @param OrganizationRequest $requestOrganization
     */
    public function injectPrimary(OrganizationRequest $requestOrganization)
    {
        $this->requestOrganization = $requestOrganization;
    }

    /**
     * @param array $conds
     * @return ICollection
     */
    public function findAllBy(array $conds)
    {
        return parent::findBy($conds);
    }

    /**
     * @param array $conds
     * @return ICollection
     */
    public function findBy(array $conds = [])
    {
        if (!isset($conds['organization'])) {
            $conds['organization'] = $this->requestOrganization->getOrganization();
        }

        return parent::findBy($conds);
    }

    /**
     * @param array $conds
     * @return ICollection
     */
    public function findByWithoutOrganization(array $conds = [])
    {
        return parent::findBy($conds);
    }

}
