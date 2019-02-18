<?php

namespace App\Modules\Admin\Grids\Places;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Search\SearchArgs;
use App\Model\Search\UseofPlaceSelector;
use Nette\Application\UI\Control;
use Nextras\Dbal\Connection;

/**
 * @author Mates
 */
class PlacesGrid extends BaseGrid
{
    /** @var UseofPlaceSelector */
    private $useofPlaceSelector;

    /** @var PositionsRepository */
    private $positionsRepository;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        Connection $connection,
        UseofPlaceSelector $useofPlaceSelector,
        PositionsRepository $positionsRepository
    )
    {
        parent::__construct($translator, $weekdayTranslator, $connection);

        $this->useofPlaceSelector = $useofPlaceSelector;
        $this->positionsRepository = $positionsRepository;
    }


    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->addColumnText('id', 'components.placesgrid.id')
            ->setSortable();

        $this->addColumnText('street_name', 'components.placesgrid.street')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('identifier', 'components.placesgrid.identifier')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('sms_keyword', 'Zóna');

        $this->addColumnText('price_per_unit', 'components.placesgrid.price')
            ->setCustomRender(function ($item) {
                $searchArgs = new SearchArgs();
                $searchArgs->reserveFrom = time();
                $useOfPlace = $this->useofPlaceSelector->getUseofPlace($item->id, $searchArgs);

                if ($useOfPlace) {
                    return $useOfPlace->pricePerUnit;
                }

                return '';
            });

        $this->addColumnText('type', 'components.placesgrid.type')
            ->setCustomRender(function ($item) {
                $searchArgs = new SearchArgs();
                $searchArgs->reserveFrom = time();
                $useOfPlace = $this->useofPlaceSelector->getUseofPlace($item->id, $searchArgs);

                if (!$useOfPlace) {
                    return '';
                }

                if ($useOfPlace->type === UseofPlaces::TYPE_RESIDENT) {
                    return $this->translator->translate('components.placesgrid.types.resident');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_HANDICAPPED) {
                    return $this->translator->translate('components.placesgrid.types.handicapped');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_SELECTED) {
                    return $this->translator->translate('components.placesgrid.types.selected');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_PROSPECT) {
                    return $this->translator->translate('components.placesgrid.types.prospect');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_AUTOMAT) {
                    return $this->translator->translate('components.placesgrid.types.automat');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_TIMEDISC) {
                    return $this->translator->translate('components.placesgrid.types.timedisc');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_FREEZONE) {
                    return $this->translator->translate('components.placesgrid.types.free');
                } elseif ($useOfPlace->type === UseofPlaces::TYPE_SHAREABLE) {
                    return $this->translator->translate('components.placesgrid.types.shared');
                }

                return '';
            });

            $this->addColumnText('lat', 'components.placesgrid.lat');
            $this->addColumnText('lng', 'components.placesgrid.lng');

            $this->addColumnNumber('capacity', 'components.placesgrid.capacity')
            ->setSortable();

            $this->addColumnText('positions', 'components.placesgrid.positionsCapacity')
            ->setCustomRender(function ($item) {
                $positionsFreeCount = 0;
                $positionsFree = $this->positionsRepository->findBy([
                    'place' => $item->id,
                    'senzorStatus' => Position::SENZOR_STATUS_FREE,
                ]);
                if ($positionsFree) {
                    $positionsFreeCount = $positionsFree->count();
                }

                return \Nette\Utils\Html::el('')->addText(
                    $positionsFreeCount . ' / ' . $item->capacity
                );
            });

            $this->addActionHref('detail', '')
            ->setIcon('pencil');

            $this->addActionHref('delete', '')
            ->setIcon('times-circle')
            ->setConfirm('components.placesgrid.deleteConfirm');

            //$this->addActionHref('addMaintenance', '')->setIcon('wrench');
            //$this->addActionHref('addInspection', '')->setIcon('user-secret');

            $this->addActionHref('addCard', '')
            ->setIcon('credit-card');
    }


    /**
     * @param int
     */
    public function setDataSource($organizationId)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->from('places');
        $builder->where('deleted_at is null');
        $builder->andWhere('organization_id = %i', $organizationId);
        $this->setModel(new NextrasDbal($this->connection, $builder));
    }

}
