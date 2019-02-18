<?php

namespace App\Modules\Admin\Grids\Organizations;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

class OrganizationSelectGrid extends BaseGrid
{
    public function create(Control $control)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->select('organizations.*');
        $builder->from('organizations');
        $builder->where('deleted_at is null');

        $this->setModel(new NextrasDbal($this->connection, $builder));

        $this->addColumnText('id', 'components.organizationsgrid.id')
            ->setSortable();

        $this->addColumnText('name', 'components.organizationsgrid.name')
            ->setSortable();

        $this->addActionHref('setRootedOrg', '')
            ->setIcon('tripadvisor');
    }
}
