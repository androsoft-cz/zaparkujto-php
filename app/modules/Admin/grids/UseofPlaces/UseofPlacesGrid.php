<?php

namespace App\Modules\Admin\Grids\UseofPlaces;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use Nette\Application\UI\Control;

class UseofPlacesGrid extends BaseGrid
{
    public function create(Control $control): void
    {
        $this->addColumnText('id', 'components.placesgrid.id')
            ->setSortable();

        $this->addColumnText('type', 'components.placesgrid.type')
            ->setSortable()
            ->setCustomRender(function ($item) {
                switch ($item->type) {
                    case UseofPlaces::TYPE_RESIDENT:
                        return $this->translator->translate('components.placesgrid.types.resident');
                        break;

                    case UseofPlaces::TYPE_HANDICAPPED:
                        return $this->translator->translate('components.placesgrid.types.handicapped');
                        break;

                    case UseofPlaces::TYPE_SELECTED:
                        return $this->translator->translate('components.placesgrid.types.selected');
                        break;

                    case UseofPlaces::TYPE_PROSPECT:
                        return $this->translator->translate('components.placesgrid.types.prospect');
                        break;

                    case UseofPlaces::TYPE_AUTOMAT:
                        return $this->translator->translate('components.placesgrid.types.automat');
                        break;

                    case UseofPlaces::TYPE_TIMEDISC:
                        return $this->translator->translate('components.placesgrid.types.timedisc');
                        break;

                    case UseofPlaces::TYPE_FREEZONE:
                        return $this->translator->translate('components.placesgrid.types.free');
                        break;

                    case UseofPlaces::TYPE_SHAREABLE:
                        return $this->translator->translate('components.placesgrid.types.shared');
                        break;

                    case UseofPlaces::TYPE_SMSZONE:
                        return $this->translator->translate('components.placesgrid.types.smszone');
                        break;

                    default:
                        return '';
                        break;
                }
            });

        $this->addColumnBinaryDays('valid_days', 'admin.validDays');
        $this->addColumnTime('valid_from', 'admin.validFrom');
        $this->addColumnTime('valid_to', 'admin.validTo');
        $this->addColumnBoolean('valid_hours_inverted', 'admin.invertValidTime');
        $this->addColumnBoolean('reserved_for_zaparkujto', 'forms.place.reservedForZaparkujto');

        $this->addActionHref('useofPlacesDetail', '', ':Admin:UseofPlaces:detail')
            ->setIcon('pencil');

        $this->addActionHref('delete', '', ':Admin:UseofPlaces:delete')
            ->setIcon('times-circle');
    }


    public function setDataSource(int $placeId): void
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->from('useof_places');
        $builder->where('deleted_at is null');
        $builder->andWhere('place_id = %i', $placeId);
        $this->setModel(new NextrasDbal($this->connection, $builder));
    }
}
