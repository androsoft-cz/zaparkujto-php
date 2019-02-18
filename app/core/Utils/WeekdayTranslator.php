<?php

namespace App\Core\Utils;

use App\Model\Utils\BinaryWeekDayCalculator;
use Kdyby\Translation\Translator;

class WeekdayTranslator
{

    /** @var Translator */
    private $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param int $weekday
     * @return string
     */
    public function translate($weekday)
    {
        switch ($weekday) {
            case 1:
                return $this->translator->trans('components.weekdays.monday');
            case 2:
                return $this->translator->trans('components.weekdays.tuesday');
            case 3:
                return $this->translator->trans('components.weekdays.wednesday');
            case 4:
                return $this->translator->trans('components.weekdays.thursday');
            case 5:
                return $this->translator->trans('components.weekdays.friday');
            case 6:
                return $this->translator->trans('components.weekdays.saturday');
            case 7:
                return $this->translator->trans('components.weekdays.sunday');
        }

        throw new \RuntimeException('Weekday "' . $weekday . '" is invalid.');
    }


    /**
     * @param int $weekday
     * @return string
     */
    public function translateShort($weekday)
    {
        switch ($weekday) {
            case 1:
                return $this->translator->trans('common.mondayShort');
            case 2:
                return $this->translator->trans('common.tuesdayShort');
            case 3:
                return $this->translator->trans('common.wednesdayShort');
            case 4:
                return $this->translator->trans('common.thursdayShort');
            case 5:
                return $this->translator->trans('common.fridayShort');
            case 6:
                return $this->translator->trans('common.saturdayShort');
            case 7:
                return $this->translator->trans('common.sundayShort');
        }

        throw new \RuntimeException('Weekday "' . $weekday . '" is invalid.');
    }


    /**
     * @param int $binaryDays 0-127
     * @return string
     */
    public function translateBinaryDays($binaryDays)
    {
        $result = '';

        for ($i = 1; $i <= 7; $i++) {
            if (BinaryWeekDayCalculator::getDayValue($binaryDays, $i)) {
                if (strlen($result)) {
                    $result .= ', ';
                }

                $result .= $this->translateShort($i);
            }
        }

        return $result;
    }

}
