<?php

namespace App\Modules\Admin\Grids\Inspections;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

/**
 * @author Mates
 */
class InspectionsGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('id', 'components.inspectionsgrid.id')
            ->setSortable();

        $this->addColumnText('street_name', 'components.inspectionsgrid.street')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('identifier', 'components.inspectionsgrid.code')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('inspected_at', 'components.inspectionsgrid.date')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('result', 'components.inspectionsgrid.result')
            ->setSortable()
            ->setFilterText();

        $this->addActionHref('detail', 'components.inspectionsgrid.detail')
            ->setIcon('pencil');

        $this->addActionHref('delete', 'components.inspectionsgrid.delete')
            ->setIcon('times-circle');
    }


    /**
     * @param int
     */
    public function setModelWithFilter($organizationId)
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('i.id, p.street_name, p.identifier, i.inspected_at, i.result')
            ->from('inspections', 'i')
            ->innerJoin('i', 'places', 'p', 'p.id = i.place_id')
            ->where('i.deleted_at is null')
            ->andWhere('[p.organization_id] = %i', $organizationId);

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }
}
