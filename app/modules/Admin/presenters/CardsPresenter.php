<?php

namespace App\Modules\Admin;

use App\Model\Facade\CardFacade;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\TimeWindows\TimeWindow;
use App\Model\Orm\TimeWindows\TimeWindowsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Modules\Admin\Forms\Cards\CardForm;
use App\Modules\Admin\Forms\Cards\ICardFormFactory;
use App\Modules\Admin\Forms\TimeWindows\ITimeWindowFormFactory;
use App\Modules\Admin\Forms\TimeWindows\TimeWindowForm;
use App\Modules\Admin\Grids\Cards\CardsGrid;
use App\Modules\Admin\Grids\Cards\ICardsGridFactory;
use App\Modules\Admin\Grids\TimeWindows\ITimeWindowsGridFactory;
use App\Modules\Admin\Grids\TimeWindows\TimeWindowsGrid;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

class CardsPresenter extends SecurePresenter
{

    /** @var ICardsGridFactory @inject */
    public $gridFactory;

    /** @var ITimeWindowsGridFactory @inject */
    public $timeWindowsGridFactory;

    /** @var ICardFormFactory @inject */
    public $formFactory;

    /** @var ITimeWindowFormFactory @inject */
    public $timeWindowsFormFactory;

    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var CardFacade @inject */
    public $cardFacade;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var TimeWindowsRepository @inject */
    public $timeWindowsRepository;


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.cards.title');
    }


    public function actionDefault()
    {
        /** @var CardsGrid $grid */
        $grid = $this->getComponent('grid');
        $grid->setModelWithFilter($this->myUserData['org_id'], ['user']);
    }


    /**
     * @param int|NULL $placeId
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionAdd($placeId = NULL)
    {
        if ($placeId !== NULL) {
            /** @var Place $place */
            $place = $this->placesRepository->getById($placeId);

            if (!$place) {
                $this->redirect('default');
            }

            $this->checkOrganizationDataAccess($place->organization);
        }

        /** @var CardForm $form */
        $form = $this->getComponent('form');
        $form->prepareForAdd($placeId);
    }


    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail($id)
    {
        /** @var User $entity */
        $entity = $this->usersRepository->getById($id);
        if (!$entity) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($entity->organization);

        /** @var CardForm $form */
        $form = $this->getComponent('form');
        $form->prepareForEdit($entity);
        $form->setDefaults($entity->contact->toArray());

        $userDefaults = $entity->toArray();
        $userDefaults['place'] = $entity->place ? $entity->place->id : NULL;
        $form->setDefaults($userDefaults);

        /** @var TimeWindowsGrid $grid */
        $grid = $this->getComponent('timeWindowsGrid');
        $grid->setModelWithFilter($id);

        $shareableEnabled = $entity->isShareableEnabled();
        $this->template->shareableEnabled = $shareableEnabled;

        if ($shareableEnabled) {
            $this->template->manualShareable = $entity->manualShareable;
            $this->template->shareable = $entity->shareable;
            $this->template->currentlyShareable = $this->cardFacade->isCurrentlyShareable($entity);
        }
    }


    /**
     * @param int
     */
    public function renderDetail($id)
    {
        $this->template->id = $id;
    }


    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionAddTimeWindow($id)
    {
        /** @var User $card */
        $card = $this->usersRepository->getById($id);
        if (!$card) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($card->organization);

        /** @var TimeWindowForm $form */
        $form = $this->getComponent('timeWindowForm');
        $form->setDefaults(['user_id' => $id]);
    }


    /**
     * @param int $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionTimeWindowDetail($id)
    {
        /** @var TimeWindow $timeWindow */
        $timeWindow = $this->timeWindowsRepository->getById($id);
        if (!$timeWindow) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($timeWindow->user->organization);

        /** @var TimeWindowForm $form */
        $form = $this->getComponent('timeWindowForm');

        $defaults = $timeWindow->toArray();

        $defaults['begin'] = $timeWindow->begin->format('%H:%I');
        $defaults['end'] = $timeWindow->end->format('%H:%I');
        $defaults['user_id'] = $timeWindow->user->id;

        $form->setDefaults($defaults);
    }


    /**
     * @param int
     */
    public function actionDeleteTimeWindow($id)
    {
        /** @var TimeWindow $timeWindow */
        $timeWindow = $this->timeWindowsRepository->getById($id);
        if (!$timeWindow) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($timeWindow->user->organization);

        $this->timeWindowsRepository->delete($id);
        $this->redirect('detail', $timeWindow->user->id);
    }


    /**
     * @param int $id
     * @param int $manualShareable  (0|1)
     */
    public function actionSetManualShareable($id, $manualShareable)
    {
        /** @var User $card */
        $card = $this->usersRepository->getById($id);
        if (!$card) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($card->organization);

        if (($card->manualShareable && !$manualShareable) || (!$card->manualShareable && $manualShareable)) {
            $card->manualShareable = $manualShareable ? 1 : 0;
            $this->usersRepository->persistAndFlush($card);
        }

        $this->flashMessage('flashmessages.recordSaved', 'success');
        $this->redirect('detail', $id);
    }


    /**
     * @param int $id
     * @param int $shareable  (0|1)
     */
    public function actionSetShareable($id, $shareable)
    {
        /** @var User $card */
        $card = $this->usersRepository->getById($id);
        if (!$card) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($card->organization);

        if (($card->shareable && !$shareable) || (!$card->shareable && $shareable)) {
            $card->shareable = $shareable ? 1 : 0;
            $this->usersRepository->persistAndFlush($card);
        }

        $this->flashMessage('flashmessages.recordSaved', 'success');
        $this->redirect('detail', $id);
    }


    /**
     * @return Grids\Cards\CardsGrid
     */
    protected function createComponentGrid()
    {
        return $this->gridFactory->create();
    }


    /**
     * @return TimeWindowsGrid
     */
    protected function createComponentTimeWindowsGrid()
    {
        return $this->timeWindowsGridFactory->create();
    }


    /**
     * @return CardForm
     */
    protected function createComponentForm()
    {
        /** @var CardForm $form */
        $form = $this->formFactory->create();
        $form->setOrganizationId($this->myUserData['org_id']);
        $form->onSuccess[] = function () {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('default');
        };

        return $form;
    }


    /**
     * @return CardForm
     */
    protected function createComponentTimeWindowForm()
    {
        /** @var TimeWindowForm $form */
        $form = $this->timeWindowsFormFactory->create();
        $form->onSuccess[] = function () use ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('detail', $form->getValues()->user_id);
        };

        return $form;
    }

}
