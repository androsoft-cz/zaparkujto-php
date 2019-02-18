<?php

namespace App\Modules\Admin\Forms\TimeWindows;

use App\Core\Forms\BaseForm;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Orm\TimeWindows\TimeWindow;
use App\Model\Orm\TimeWindows\TimeWindowsRepository;
use App\Model\Orm\Users\UsersRepository;
use DateInterval;

final class TimeWindowForm extends BaseForm
{

    /** @var TimeWindowsRepository */
    private $timeWindowsRepository;

    /** @var UsersRepository */
    private $usersRepository;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        TimeWindowsRepository $timeWindowsRepository,
        UsersRepository $usersRepository
    )
    {
        parent::__construct();
        $this->setTranslator($translator);

        $this->timeWindowsRepository = $timeWindowsRepository;
        $this->usersRepository = $usersRepository;

        $weekdayItems = [];

        for ($weekday = 1; $weekday <= 7; $weekday++) {
            $weekdayItems[$weekday] = $weekdayTranslator->translate($weekday);
        }

        $this->addSelect('weekday', 'forms.timewindow.weekday', $weekdayItems)
            ->setRequired('forms.timewindow.rule.weekday');

        $this->addText('begin', 'forms.timewindow.begin')
            ->setDefaultValue('00:00')
            ->setType('time')
            ->setRequired('forms.timewindow.rule.begin');

        $this->addText('end', 'forms.timewindow.end')
            ->setDefaultValue('23:59')
            ->setType('time')
            ->setRequired('forms.timewindow.rule.end');

        $this->addHidden('id');
        $this->addHidden('user_id');
        $this->addSubmit('submit', 'forms.card.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm()
    {
        $values = $this->getValues();

        if ($values->id != '') { // update
            /** @var TimeWindow $timeWindow */
            $timeWindow = $this->timeWindowsRepository->getById($values->id);
        } else { // insert
            $user = $this->usersRepository->getById($values->user_id);

            $timeWindow = new TimeWindow();
            $timeWindow->user = $user;
        }

        $timeWindow->weekday = (int) $values->weekday;

        list($hours, $minutes) = sscanf($values->begin, '%d:%d');
        $timeWindow->begin = new DateInterval("PT{$hours}H{$minutes}M0S");

        list($hours, $minutes) = sscanf($values->end, '%d:%d');
        $timeWindow->end = new DateInterval("PT{$hours}H{$minutes}M0S");

        $this->timeWindowsRepository->persistAndFlush($timeWindow);
    }

}
