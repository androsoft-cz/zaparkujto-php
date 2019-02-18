<?php

namespace App\Modules\Admin\Grids\SenzorHistory;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\SenzorLogs\SenzorLogsRepository;
use Grido\Components\Columns\Date;
use Grido\Components\Filters\Filter;
use Nette\Application\UI\Control;
use Nextras\Dbal\Connection;

class SenzorHistoryGrid extends BaseGrid
{
    /** @var SenzorLogsRepository */
    private $senzorLogsRepository;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        Connection $connection,
        SenzorLogsRepository $senzorLogsRepository
    )
    {
        parent::__construct($translator, $weekdayTranslator, $connection);

        $this->senzorLogsRepository = $senzorLogsRepository;
    }


    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->setDefaultSort(['id' => 'DESC']);
        $this->setFilterRenderType(Filter::RENDER_INNER);

        $this->addColumnText('id', 'components.historysenzorgrid.id')
            ->setSortable();

        $this->addColumnText('device', 'components.historysenzorgrid.device')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('lat', 'components.historysenzorgrid.lat');

        $this->addColumnText('lng', 'components.historysenzorgrid.lng');

        $this->addColumnText('status', 'components.historysenzorgrid.status')
            ->setCustomRender(function ($item) {
                if ($item->status === Position::SENZOR_STATUS_FREE) {
                    return $this->translator->translate('components.historysenzorgrid.statusFree');
                } elseif ($item->status === Position::SENZOR_STATUS_FULL) {
                    return $this->translator->translate('components.historysenzorgrid.statusFull');
                }

                return $this->translator->translate('components.historysenzorgrid.statusUndefined');
            })
            ->setSortable();

        $this->addColumnDate('created_at', 'components.historysenzorgrid.createdAt', Date::FORMAT_DATETIME)
            ->setFilterDate();
    }


    public function setDataSource(Position $position)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->from('senzor_logs');
        $builder->andWhere('position_id = %i', $position->id);
        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
