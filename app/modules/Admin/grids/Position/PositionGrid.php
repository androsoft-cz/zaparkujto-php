<?php

namespace App\Modules\Admin\Grids\Position;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;
use Grido\Components\Columns\Date;
use Grido\Components\Filters\Filter;
use Nette\Application\UI\Control;
use Nextras\Dbal\Connection;

class PositionGrid extends BaseGrid
{
    /** @var PositionsRepository */
    private $positionsRepository;

    /** @var Place */
    private $place;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        Connection $connection,
        PositionsRepository $positionsRepository
    )
    {
        parent::__construct($translator, $weekdayTranslator, $connection);

        $this->positionsRepository = $positionsRepository;
    }


    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->setDefaultSort(['id' => 'ASC']);
        $this->setFilterRenderType(Filter::RENDER_INNER);

        $this->addColumnText('id', 'components.positiongrid.id')
            ->setSortable();

        $this->addColumnText('senzor_number', 'components.positiongrid.senzorNumber')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('senzor_name', 'components.positiongrid.senzorName')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('senzor_status', 'components.positiongrid.senzorStatus')
            ->setCustomRender(function ($item) {
                $text = $this->translator->translate('components.positiongrid.senzorStatusUndefined');

                if ($item->senzor_status === Position::SENZOR_STATUS_FREE) {
                    $text = $this->translator->translate('components.positiongrid.senzorStatusFree');
                } elseif ($item->senzor_status === Position::SENZOR_STATUS_FULL) {
                    $text = $this->translator->translate('components.positiongrid.senzorStatusFull');
                }

                return \Nette\Utils\Html::el('a', [
                    'href' => $this->getPresenter()->link('positionSenzorHistory', [
                        'positionId' => $item->id,
                        'placeId' => $item->place_id,
                    ]),
                ])->addText($text);
            })
            ->setSortable();

        $this->addColumnDate('senzor_status_changed_at', 'components.positiongrid.senzorStatusCahngedAt', Date::FORMAT_DATETIME)
            ->setFilterDate();

        $this->addActionHref('positionDetail', '', 'positionDetail', ['placeId' => $this->place->id])
            ->setIcon('pencil');
    }


    public function setDataSource(Place $place)
    {
        $this->place = $place;

        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->from('positions');
        $builder->andWhere('place_id = %i', $place->id);
        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
