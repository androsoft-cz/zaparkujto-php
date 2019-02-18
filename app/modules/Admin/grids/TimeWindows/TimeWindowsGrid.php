<?php

namespace App\Modules\Admin\Grids\TimeWindows;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use Nette\Application\UI\Control;

final class TimeWindowsGrid extends BaseGrid
{

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnWeekday('weekday', 'components.timewindowsgrid.weekday');
        $this->addColumnTime('begin', 'components.timewindowsgrid.begin');
        $this->addColumnTime('end', 'components.timewindowsgrid.end');

        $this->addActionHref('timeWindowDetail', '')
            ->setIcon('pencil');
        $this->addActionHref('deleteTimeWindow', '')
            ->setIcon('times-circle');
    }


    /**
     * @param int $cardId
     */
    public function setModelWithFilter($cardId)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('time_windows')
            ->where('user_id = %i', $cardId)
            ->andWhere('deleted_at is null')
            ->orderBy('weekday');

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
