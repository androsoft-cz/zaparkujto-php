<?php

namespace App\Core\Template;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory as NTemplateFactory;
use Nette\Caching\IStorage;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Security\User;

final class TemplateFactory extends NTemplateFactory implements ITemplateFactory
{

    /** @var LinkGenerator */
    private $linkGenerator;

    /**
     * @param ILatteFactory $latteFactory
     * @param IRequest $httpRequest
     * @param IResponse $httpResponse
     * @param User $user
     * @param IStorage $cacheStorage
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(
        ILatteFactory $latteFactory,
        IRequest $httpRequest,
        User $user,
        IStorage $cacheStorage,
        LinkGenerator $linkGenerator)
    {
        parent::__construct($latteFactory, $httpRequest, $user, $cacheStorage);

        $this->linkGenerator = $linkGenerator;
    }


    /**
     * @return ITemplate
     */
    public function createMailTemplate()
    {
        $template = $this->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);

        return $template;
    }

}
