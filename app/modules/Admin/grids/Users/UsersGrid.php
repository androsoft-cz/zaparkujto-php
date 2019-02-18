<?php

namespace App\Modules\Admin\Grids\Users;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

final class UsersGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('username', 'components.usersgrid.login')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('name', 'components.usersgrid.name')
            ->setSortable()
            ->setFilterText();

        $this->addActionHref('detail', '')
            ->setIcon('pencil');

        $this->addActionHref('delete', '')
            ->setIcon('times-circle');

    }

    public function setModelWithFilter($organizationId, $roles)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->from('users');
        $builder->where('organization_id = %i', $organizationId);
        $builder->andWhere('role IN %s[]', $roles);
        $this->setModel(new NextrasDbal($this->connection, $builder));
    }
}
