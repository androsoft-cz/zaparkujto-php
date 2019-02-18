<?php

namespace App\Modules\Admin\Grids\Organizations;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

/**
 * @author Mates
 */
class OrganizationsGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->select('organizations.*, contacts.address1, contacts.address2, contacts.identification_number');
        $builder->from('organizations');
        $builder->leftJoin('organizations', 'contacts', NULL, 'organizations.contact_id = contacts.id');
        $builder->where('deleted_at is null');

        $this->setModel(new NextrasDbal($this->connection, $builder));

        $this->addColumnText('id', 'components.organizationsgrid.id')
            ->setSortable();

        $this->addColumnText('name', 'components.organizationsgrid.name')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('address1', 'components.organizationsgrid.address1')
            ->setSortable()
            ->setFilterText();
        $this->addColumnText('address2', 'components.organizationsgrid.address2')
            ->setSortable()
            ->setFilterText();
        $this->addColumnText('identification_number', 'components.organizationsgrid.cin')
            ->setSortable()
            ->setFilterText();

        $this->addActionHref('detail', '')
            ->setIcon('pencil');

        $this->addActionHref('delete', '')
            ->setIcon('times-circle')
            ->setConfirm('components.organizationsgrid.deleteConfirm');

        $this->addActionHref('setMyOrg', '')
            ->setIcon('tripadvisor');

    }

}
