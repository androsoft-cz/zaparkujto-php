<?php

namespace App\Modules\Admin\Grids\PlaceCards;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

final class PlaceCardsGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('username', 'components.placecardsgrid.username')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('name', 'components.placecardsgrid.fullName')
            ->setSortable()
            ->setFilterText();

        $this->addActionHref('removeCard', '')
            ->setIcon('times-circle');
    }


    /**
     * @param int $placeId
     * @param string[] $roles
     */
    public function setModelWithFilter($placeId, $roles)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder()
            ->select('users.id, users.place_id, users.name, users.username')
            ->from('users')
            ->where('users.place_id = %i', $placeId)
            ->andWhere('users.role IN %s[]', $roles);

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
