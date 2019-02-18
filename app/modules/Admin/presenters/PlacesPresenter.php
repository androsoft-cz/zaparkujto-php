<?php

namespace App\Modules\Admin;

use App\Model\Exceptions\Runtime\ResumableJs\FileUploadException;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Orm\Positions\Position;
use App\Model\Orm\Positions\PositionsRepository;
use App\Model\Orm\SenzorLogs\SenzorLogsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\WebServices\Locator\PlaceImageLocator;
use App\Model\WebServices\ResumableJs\UploadHandler;
use App\Modules\Admin\Forms\PlaceCards\IPlaceCardFormFactory;
use App\Modules\Admin\Forms\PlaceCards\PlaceCardForm;
use App\Modules\Admin\Forms\Places\IPlaceFormFactory;
use App\Modules\Admin\Forms\Positions\IPositionFormFactory;
use App\Modules\Admin\Forms\Positions\PositionForm;
use App\Modules\Admin\Grids\PlaceCards\IPlaceCardsGridFactory;
use App\Modules\Admin\Grids\PlaceCards\PlaceCardsGrid;
use App\Modules\Admin\Grids\Places\IPlacesGridFactory;
use App\Modules\Admin\Grids\Position\IPositionGridFactory;
use App\Modules\Admin\Grids\UseofPlaces\IUseofPlacesGridFactory;
use App\Modules\Admin\Grids\UseofPlaces\UseofPlacesGrid;
use App\Modules\Admin\Grids\SenzorHistory\ISenzorHistoryGridFactory;
use Nextras\Dbal\Connection;
use Nextras\Orm\Entity\AbstractEntity;

class PlacesPresenter extends SecurePresenter
{
    /** @var IPlacesGridFactory @inject */
    public $gridFactory;

    /** @var IPlaceCardsGridFactory @inject */
    public $placeCardsGridFactory;

    /** @var IUseofPlacesGridFactory @inject */
    public $useofPlacesGridFactory;

    /** @var ISenzorHistoryGridFactory @inject */
    public $senzorHistoryGridFactory;

    /** @var IPositionGridFactory @inject */
    public $positionGridFactory;

    /** @var IPlaceFormFactory @inject */
    public $formFactory;

    /** @var IPlaceCardFormFactory @inject */
    public $placeCardFormFactory;

    /** @var IPositionFormFactory @inject */
    public $positionFormFactory;

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var Connection @inject */
    public $connection;

    /** @var UploadHandler @inject */
    public $uploadHandler;

    /** @var PlaceImageLocator @inject */
    public $placeImageLocator;

    /** @var SenzorLogsRepository @inject */
    public $senzorLogsRepository;

    /** @var PositionsRepository @inject */
    public $positionsRepository;

    /** @var OrganizationsRepository @inject */
    public $organizationsRepository;

    /** @var Place */
    private $place;

    /** @var Position */
    private $position;


    public function startup()
    {
        parent::startup();
        $organization = $this->organizationsRepository->findById($this->myUserData['org_id'])->fetch();
        $this->checkOrganizationDataAccess($organization);
    }


    public function beforeRender()
    {
        $this->template->navbarTitle = $this->getTranslator()->translate('presenters.places.title');
    }


    public function createComponentGrid()
    {
        $grid = $this->gridFactory->create();
        $grid->setDataSource($this->myUserData['org_id']);

        return $grid;
    }


    public function createComponentPlaceCardsGrid()
    {
        $grid = $this->placeCardsGridFactory->create();

        return $grid;
    }


    public function createComponentPositionGrid()
    {
        $grid = $this->positionGridFactory->create();
        $grid->setDataSource($this->place);

        return $grid;
    }


    public function createComponentSenzorHistoryGrid()
    {
        $grid = $this->senzorHistoryGridFactory->create();
        $grid->setDataSource($this->position);

        return $grid;
    }


    /**
     * @return UseofPlacesGrid
     */
    public function createComponentUseofPlacesGrid()
    {
        $grid = $this->useofPlacesGridFactory->create();
        $grid->setDataSource($this->place->id);

        return $grid;
    }


    public function actionDetail($id)
    {
        $this->place = $this->placesRepository->getById($id);

        if (!$this->place) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($this->place->organization);

        $defaults = $this->place->toArray(AbstractEntity::TO_ARRAY_RELATIONSHIP_AS_ID);

        $this->getComponent('form')->setDefaults($defaults);

        /** @var PlaceCardsGrid $grid */
        $grid = $this->getComponent('placeCardsGrid');
        $grid->setModelWithFilter($id, ['user']);
    }


    public function renderDetail($id = NULL)
    {
        $this->template->placeId = $this->place->id;
        $this->template->place = $this->place;
        $this->template->allowedExtensions = $this->placeImageLocator->getAllowedExtensions();

        if (!isset($this->template->imageUrl)) {
            $this->template->imageUrl = $this->placeImageLocator->getUrl($id);
        }

        $positionsFreeCount = 0;
        $positionsFree = $this->positionsRepository->findBy([
            'place' => $this->place->id,
            'senzorStatus' => Position::SENZOR_STATUS_FREE,
        ]);
        if ($positionsFree) {
            $positionsFreeCount = $positionsFree->count();
        }

        $this->template->positionsFreeCount = $positionsFreeCount;
        $this->template->cardCount = $this->place->users->count();
    }


    public function actionPositionDetail($id, $placeId)
    {
        $this->position = $this->positionsRepository->getBy([
            'id' => $id,
            'place' => $placeId,
        ]);

        if (!$this->position) {
            $this->redirect('default');
        }

        $this->place = $this->position->place;

        $this->getComponent('positionForm')->setDefaults($this->position->toArray(AbstractEntity::TO_ARRAY_RELATIONSHIP_AS_ID));

        $this->template->position = $this->position;
    }


    public function actionPositionAdd($placeId)
    {
        $this->place = $this->placesRepository->getById($placeId);

        if (!$this->place) {
            $this->redirect('default');
        }

        $this->template->place = $this->place;
    }


    public function actionPositionSenzorHistory($positionId, $placeId)
    {
        $this->position = $this->positionsRepository->getBy([
            'id' => $positionId,
            'place' => $placeId,
        ]);

        if (!$this->position) {
            $this->redirect('default');
        }

        $this->place = $this->position->place;

        $this->template->position = $this->position;
    }


    public function actionDelete($id)
    {
        /** @var Place $place */
        $place = $this->placesRepository->getById($id);

        if (!$place) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($place->organization);

        $this->placesRepository->delete($id);
        $this->redirect('default');
    }


    public function actionRemoveCard($id)
    {
        /** @var User $card */
        $card = $this->usersRepository->getById($id);

        if (!$card || !$card->place) {
            $this->redirect('default');
        }

        $this->checkOrganizationDataAccess($card->organization);

        $placeId = $card->place->id;
        $card->place = NULL;
        $this->usersRepository->persistAndFlush($card);

        $this->redirect('detail', [$placeId]);
    }


    public function handleCardAutoComplete($term)
    {
        $result = [];

        if (strlen($term) > 1) {
            $cards = $this->usersRepository->findByUsernameLike($term, $this->myUserData['org_id'], ['user'], 5);

            foreach ($cards as $card) {
                $result[] = $card->username;
            }
        }

        $this->sendJson($result);
    }


    public function createComponentForm()
    {
        $form = $this->formFactory->create();
        $form->setSparePlaces($this->myUserData['org_id']);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('default');
        };

        return $form;
    }


    public function createComponentPlaceCardForm()
    {
        /** @var PlaceCardForm $form */
        $form = $this->placeCardFormFactory->create();
        $form->setOrganizationId($this->myUserData['org_id']);
        $form->setPlaceId($this->place->id);

        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('detail', ['id' => $this->place->id]);
        };

        return $form;
    }


    public function createComponentPositionForm()
    {
        /** @var PositionForm $form */
        $form = $this->positionFormFactory->create();
        $form->setPlace($this->place);

        $form->onSuccess[] = function ($form) {
            $this->flashMessage('flashmessages.recordSaved', 'success');
            $this->redirect('detail', ['id' => $this->place->id]);
        };

        return $form;
    }


    public function actionAddInspection($id)
    {
        $this->redirect(':Admin:Inspections:Add', $id);
    }


    public function actionAddMaintenance($id)
    {
        $this->redirect(':Admin:Maintenances:Add', $id);
    }


    public function actionAddCard($id)
    {
        $this->redirect(':Admin:Cards:Add', ['placeId' => $id]);
    }


    public function actionProceedChunk($id)
    {
        try {
            $responseCode = $this->uploadHandler->handle(
                $this->request,
                $this->placeImageLocator->getPath($id),
                $this->placeImageLocator->getAllowedExtensions()
            );

            if ($responseCode !== NULL) {
                $this->getHttpResponse()->setCode($responseCode);
            }
        } catch (FileUploadException $e) {
            // No reason to handle or log FileUploadException. It occurs when someone try to fake the request.
        }

        $this->terminate();
    }


    /**
     * @param int $id
     * @return string|null
     */
    public function getImageUrl($id)
    {
        return $this->placeImageLocator->getUrl($id);
    }


    public function handleUpdateImage($id)
    {
        $this->template->imageUrl = $this->placeImageLocator->getUrl($id) . '?t=' . time(); // time() - force image refresh
        $this->redrawControl('image');
    }
}
