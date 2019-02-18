<?php

namespace App\Modules\Admin\Grids\Maintenances;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

/**
 * @author Mates
 */
class MaintenancesGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('id', 'components.maintenancesgrid.id')
            ->setSortable();

        $this->addColumnText('street_name', 'components.maintenancesgrid.street')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('identifier', 'components.maintenancesgrid.code')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('maintenanced_at', 'components.maintenancesgrid.date')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('maintenance_result', 'components.maintenancesgrid.result')
            ->setSortable()
            ->setFilterText();

        $this->addActionHref('detail', 'components.maintenancesgrid.detail')
            ->setIcon('pencil');

        $this->addActionHref('delete', 'components.maintenancesgrid.delete')
            ->setIcon('times-circle');
    }


    /**
     * @param int
     */
    public function setModelWithFilter($organizationId)
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('m.id, p.street_name, p.identifier, m.maintenanced_at, m.maintenance_result')
            ->from('maintenances', 'm')
            ->innerJoin('m', 'places', 'p', 'p.id = m.place_id')
            ->where('m.deleted_at is null')
            ->andWhere('[p.organization_id] = %i', $organizationId);

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }
}
