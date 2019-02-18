<?php

namespace App\Modules\Admin\Forms\Positions;

use App\Core\Forms\BaseForm;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;

final class PositionForm extends BaseForm
{
    /** @var PositionsRepository */
    private $positionsRepository;

    /** @var Place */
    private $place;


    public function __construct(
        \Kdyby\Translation\Translator $translator,
        PositionsRepository $positionsRepository
    )
    {
        parent::__construct();

        $this->setTranslator($translator);

        $this->positionsRepository = $positionsRepository;

        $this->addGroup('');
        $this->addText('senzorNumber', 'forms.position.senzorNumber');
        $this->addText('senzorName', 'forms.position.senzorName');

        $this->addHidden('id');
        $this->addSubmit('submit', 'forms.position.submit');

        $this->onSuccess[] = [$this, 'processForm'];
    }


    public function processForm()
    {
        $values = $this->getValues();

        if ($values->id != '') {
            /** @var Position $position */
            $position = $this->positionsRepository->getById($values->id);
        } else {
            /** @var Position $position */
            $position = new Position;
            $position->place = $this->place;
        }

        $position->senzorNumber = $values->senzorNumber;
        $position->senzorName = $values->senzorName;

        try {
            $this->positionsRepository->persistAndFlush($position);
        } catch (\Nextras\Dbal\UniqueConstraintViolationException $e) {
            $this->addError($this->getTranslator()->translate('forms.position.rule.senzorNumber'));
        }
    }


    public function setPlace(Place $place)
    {
        $this->place = $place;
    }

}
