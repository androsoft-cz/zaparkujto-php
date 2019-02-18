<?php

namespace App\Modules\Driver\Forms\Extend;

use App\Core\Forms\BaseForm;

final class ExtendReservationForm extends BaseForm
{

    const TO_1_HOUR = 60;
    const TO_2_HOURS = 120;
    const TO_3_HOURS = 180;
    const TO_CUSTOM = 0;

    public function __construct()
    {
        parent::__construct();

        $this->addText('from', 'Od')
            ->setDisabled();

        $this->addText('to', 'Do')
            ->setDisabled();

        $this->addRadioList('extra', 'Prodloužit o')
            ->setItems([
                self::TO_1_HOUR => '1h',
                self::TO_CUSTOM => 'do',
                self::TO_2_HOURS => '2h',
                self::TO_3_HOURS => '3h',
            ])
            ->setDefaultValue(self::TO_1_HOUR)
            ->setRequired('Vyberte prosím čas do');

        $this->addText('price', 'Cena (původní)')
            ->setDisabled();
        $this->addText('extraprice', 'Cena (navíc)')
            ->setDisabled();

        $this->addSubmit('extend1', 'Zaplatit kartou');
        $this->addSubmit('extend2', 'Zaplatit převodem');
    }

    /**
     * @param array $values
     * @param bool|FALSE $erase
     * @return ExtendReservationForm
     */
    public function setDefaults($values, $erase = FALSE)
    {
        if (isset($values['from'])) {
            $values['from'] = date('d.m.Y - H:i', is_numeric($values['from']) ? $values['from'] : strtotime($values['from']));
        }

        if (isset($values['to'])) {
            $values['to'] = date('d.m.Y - H:i', is_numeric($values['to']) ? $values['to'] : strtotime($values['to']));
        }

        return parent::setDefaults($values, $erase);
    }

}
