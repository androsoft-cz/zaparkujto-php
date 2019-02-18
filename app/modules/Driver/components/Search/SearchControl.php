<?php

namespace App\Modules\Driver\Components\Search;

use App\Core\UI\BaseControl;
use App\Model\Orm\UseofPlaces\UseofPlaces;
use App\Model\Search\SearchArgs;
use App\Model\Search\SearchService;
use App\Model\Search\SearchSessionService;
use App\Modules\Driver\Components\Geocode\AutoGeocodeControl;
use App\Modules\Driver\Components\Geocode\IAutoGeocodeControlFactory;
use Kdyby\Translation\ITranslator;
use Nette\Application\UI\Form;

final class SearchControl extends BaseControl
{
    /** @var array */
    public $onNotFound = [];

    /** @var array */
    public $onSearch = [];

    /** @var SearchService */
    private $searchService;

    /** @var SearchSessionService */
    private $searchSessionService;

    /** @var SearchArgs */
    private $searchArgs;

    /** @var IAutoGeocodeControlFactory */
    private $geocodeControlFactory;

    /** @var ITranslator */
    private $translator;


    /**
     * @param SearchService $searchService
     * @param SearchSessionService $searchSessionService
     * @param SearchArgs $searchArgs
     * @param IAutoGeocodeControlFactory $autoGeocodeControlFactory
     * @param ITranslator $translator
     */
    public function __construct(
        SearchService $searchService,
        SearchSessionService $searchSessionService,
        SearchArgs $searchArgs,
        IAutoGeocodeControlFactory $autoGeocodeControlFactory,
        ITranslator $translator
    )
    {
        parent::__construct();
        $this->searchService = $searchService;
        $this->searchSessionService = $searchSessionService;
        $this->searchArgs = $searchArgs;
        $this->geocodeControlFactory = $autoGeocodeControlFactory;
        $this->translator = $translator;

        // Enable HTML5 navigator (first time)
        if (!$this->searchSessionService->exists(SearchSessionService::NAVIGATOR)) {
            $this->searchSessionService->store(SearchSessionService::NAVIGATOR, TRUE);
        }
    }

    /**
     * SEARCH FORM *************************************************************
     */

    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);

        if ($this->searchSessionService->restore(SearchSessionService::NAVIGATOR) === TRUE) {
            $form->getElementPrototype()->setAttribute('data-autocoords', $this->link('geocode-auto!'));
            $form->getElementPrototype()->setAttribute('data-ns', $this['geocode']->getUniqueId() . '-');
        }

        $form->addText('geoplace', 'Kde chcete zaparkovat')
            ->setRequired('Musíte vyplnit kde chcete parkovat.');

        $form->addCheckboxList('types', $this->translator->translate('forms.place.type'), [
            UseofPlaces::TYPE_RESIDENT => $this->translator->translate('forms.place.types.resident'),
            UseofPlaces::TYPE_HANDICAPPED => $this->translator->translate('forms.place.types.hendicapped'),
            UseofPlaces::TYPE_AUTOMAT => $this->translator->translate('forms.place.types.automat'),
            UseofPlaces::TYPE_TIMEDISC => $this->translator->translate('forms.place.types.timedisc'),
            UseofPlaces::TYPE_SELECTED => $this->translator->translate('forms.place.types.selected'),
            UseofPlaces::TYPE_FREEZONE => $this->translator->translate('forms.place.types.free'),
            UseofPlaces::TYPE_SHAREABLE => $this->translator->translate('forms.place.types.shared'),
        ]);

        $form->addHidden('lnglat');
        $form->addHidden('place');

        $form->addSubmit('search', 'Vyhledat');
        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function processForm(Form $form)
    {
        if ($form->values->lnglat) {
            list ($lng, $lat) = explode(',', $form->values->lnglat);
        } else {
            $result = $this->searchService->geocode($form->values->geoplace);

            if (!$result) {
                // Pass to onNotFound event
                $this->onNotFound();

                return;
            }

            $lng = $result->geometry->location->lng;
            $lat = $result->geometry->location->lat;
        }

        // Pass to onSearch event
        $this->onSearch([
            'q' => $form->values->geoplace,
            'lat' => $lat,
            'lng' => $lng,
            'types' => $form->values->types,
        ]);
    }

    /**
     * @param array $values
     */
    public function setFormDefaults($values)
    {
        $this['form']->setDefaults($values, FALSE);
    }

    /**
     * GEOCODE *****************************************************************
     */

    /**
     * @return AutoGeocodeControl
     */
    protected function createComponentGeocode()
    {
        $geocode = $this->geocodeControlFactory->create();

        $geocode->onError[] = function () {
            $this->searchSessionService->store(SearchSessionService::NAVIGATOR, FALSE);
            $this->redirect('this');
        };

        $geocode->onGeocode[] = function ($q, $lng, $lat) {
            // Re-pass to onSearch event
            $this->onSearch([
                'q' => $q,
                'lat' => $lat,
                'lng' => $lng,
            ]);
        };

        return $geocode;
    }

    /**
     * RENDERING ***************************************************************
     */

    /**
     * Render template
     */
    public function render()
    {
        // Fill form defaults
        $this->setFormDefaults([
            'geoplace' => $this->searchArgs->q,
            'lnglat' => $this->searchArgs->lng && $this->searchArgs->lat ? $this->searchArgs->lng . ',' . $this->searchArgs->lat : NULL,
            'types' => is_array($this->searchArgs->type) ? $this->searchArgs->type : [],
        ]);

        // Render template
        $this->template->setFile(__DIR__ . '/templates/search.latte');
        $this->template->render();
    }

}
