<?php

namespace App\Modules\Admin\Grids\Credits;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\Columns\CustomLabeledNumber;
use App\Core\Grids\Grido\NextrasDbal;
use App\Model\Orm\Credits\Credit;
use Grido\Components\Columns\Column;
use Grido\Components\Columns\Date;
use Grido\Components\Filters\Filter;
use Nette\Application\UI\Control;

final class CreditsGrid extends BaseGrid
{

    /** @var string[]  access always via getMovementTypeTranslations() method */
    private $movementTypeTranslations = NULL;


    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->setFilterRenderType(Filter::RENDER_INNER);
        $this->setDefaultSort(['credits.created_at' => 'DESC']);
        $this->setExport();

        $this->addColumnText('username', 'components.creditsgrid.username')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('name', 'components.creditsgrid.name')
            ->setSortable()
            ->setFilterText();

        $movementTypeTranslations = $this->getMovementTypeTranslations();

        $renderCallback = function ($item) use ($movementTypeTranslations) {
            if (isset($movementTypeTranslations[(int) $item->movement_type])) {
                return $this->translator->translate($movementTypeTranslations[(int) $item->movement_type]);
            }

            return '';
        };

        $this->addColumnText('movement_type', 'common.type')
            ->setSortable()
            ->setCustomRender($renderCallback)
            ->setCustomRenderExport($renderCallback)
            ->setFilterSelect([NULL => ''] + $movementTypeTranslations);

        $this->addColumnCustomLabeledNumber('price', 'components.creditsgrid.price')
            ->setSortable()
            ->setFilterNumber();

        $this->addColumnDate('created_at', 'components.creditsgrid.createdAt', Date::FORMAT_DATETIME)
            ->setFilterDateRange()
            ->setColumn('credits.created_at');

        $this->onRender[] = function () {
            $data = $this->data;
            $totalPrice = $this->getTotalPrice($data);

            /** @var CustomLabeledNumber $priceColumn */
            $priceColumn = $this['columns']->getComponent('price');

            $priceColumn->setCustomLabel($this->translator->translate('components.creditsgrid.price') . " ($totalPrice)");
        };
    }


    /**
     * @param int
     */
    public function setModelWithFilter($organizationId)
    {
        $movementTypes = [
            Credit::MOVEMENT_TYPE_CHARGE_BY_GATE,
            Credit::MOVEMENT_TYPE_CHARGE_BY_CLAIM,
        ];

        /** @var $builder */
        $builder = $this->connection->createQueryBuilder()
            ->select('credits.id, credits.price, credits.movement_type, credits.created_at, users.name, users.username')
            ->from('credits')
            ->leftJoin('credits', 'users', 'users', 'credits.user_id = users.id')
            ->where('users.organization_id = %i', $organizationId)
            ->andWhere('credits.price > 0')
            ->andWhere('credits.deleted_at IS NULL')
            ->andWhere('credits.movement_type IN %i[]', $movementTypes);

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }


    public function handleExportToXls()
    {
        $escape = function ($value) {
            return preg_match("~[\"\n,;\t]~", $value) || $value === '' ? '"' . str_replace('"', '""', $value) . '"' : $value;
        };

        $phpExcel = new \PHPExcel;
        $phpExcel->setActiveSheetIndex(0);

        $data = $this->getData(FALSE, FALSE, FALSE)->getData();
        $totalPrice = $this->getTotalPrice($data);

        /** @var Column[] $columns */
        $columns = $this['columns']->getComponents();
        $columnCount = 0;

        foreach ($columns as $column) {
            $label = $column->getLabel();

            if ($column->getColumn() == 'price') {
                $label .= " ($totalPrice)";
            }

            $phpExcel->getActiveSheet()->setCellValueByColumnAndRow($columnCount, 1, $escape($label));
            $columnCount++;
        }

        $rowCount = 2; // skip headers

        $movementTypeTranslations = $this->getMovementTypeTranslations();

        foreach ($data as $item) {
            $movementType = isset($movementTypeTranslations[(int) $item->movement_type]) ? $movementTypeTranslations[(int) $item->movement_type] : '';

            $phpExcel->getActiveSheet()->setCellValue('A' . $rowCount, $escape($item->username));
            $phpExcel->getActiveSheet()->setCellValue('B' . $rowCount, $escape($item->name));
            $phpExcel->getActiveSheet()->setCellValue('C' . $rowCount, $movementType);
            $phpExcel->getActiveSheet()->setCellValue('D' . $rowCount, $escape($item->price));
            $phpExcel->getActiveSheet()->setCellValue('E' . $rowCount, $item->created_at->format(Date::FORMAT_DATETIME));
            $rowCount++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . time() . '.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $phpExcelWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $phpExcelWriter->save('php://output');
    }


    /**
     * @return string[]
     */
    private function getMovementTypeTranslations()
    {
        if ($this->movementTypeTranslations === NULL) {
            $this->movementTypeTranslations = [
                Credit::MOVEMENT_TYPE_CHARGE_BY_GATE => $this->translator->translate('components.creditsgrid.typeChargeByGate'),
                Credit::MOVEMENT_TYPE_CHARGE_BY_CLAIM => $this->translator->translate('components.creditsgrid.chargeByClaim'),
            ];
        }

        return $this->movementTypeTranslations;
    }


    /**
     * @param array $data
     * @return int
     */
    public function getTotalPrice($data)
    {
        $totalPrice = 0;

        foreach ($data as $item) {
            $totalPrice += $item->price;
        }

        return $totalPrice;
    }

}
