<?php

namespace App\Core\Components;

use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{

    /**
     * @return \Kdyby\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->getPresenter()->getTranslator();
    }

}
