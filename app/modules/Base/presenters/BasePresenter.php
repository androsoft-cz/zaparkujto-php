<?php

namespace App\Modules\Base;

use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;


    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->translator = $this->getTranslator();
    }


    public function flashMessage($message, $type = 'info')
    {
        $message = $this->getTranslator()->translate($message);

        return parent::flashMessage($message, $type);
    }


    /**
     * @return \Kdyby\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

}
