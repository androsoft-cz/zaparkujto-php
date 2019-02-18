<?php

namespace App\Core\Template;

use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory as NITemplateFactory;

interface ITemplateFactory extends NITemplateFactory
{

    /**
     * @return ITemplate
     */
    public function createMailTemplate();

}
