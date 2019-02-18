<?php

namespace App\Modules\Admin\Grids\Cards;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

final class CardsGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('username', 'components.cardsgrid.username')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('rz', 'components.cardsgrid.rz')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('company', 'components.cardsgrid.company')
            ->setColumn('company')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('identification_number', 'components.cardsgrid.cin')
            ->setSortable()
            ->setFilterText();

        $this->addColumnBoolean('handicapped', 'components.cardsgrid.handicapped');

        $this->addActionHref('detail', '')
            ->setIcon('pencil');
    }


    /**
     * @param int
     * @param array
     */
    public function setModelWithFilter($organizationId, $roles)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder()
            ->select('users.id, users.handicapped, users.username, contacts.company, contacts.identification_number, GROUP_CONCAT(users_rz.rz SEPARATOR ", ") AS rz')
            ->from('users')
            ->leftJoin('users', 'contacts', 'contacts', 'users.contact_id = contacts.id')
            ->leftJoin('users', 'users_rz', 'users_rz', 'users.id = users_rz.user_id')
            ->where('users.organization_id = %i', $organizationId)
            ->andWhere('users.role IN %s[]', $roles)
            ->groupBy('users.id');

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
