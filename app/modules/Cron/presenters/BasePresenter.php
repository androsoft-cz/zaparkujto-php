<?php

namespace App\Modules\Cron;

use App\Core\Cron\Http\TokenValidator;
use App\Modules\Base\BasePresenter as BaseBase;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\TextResponse;

abstract class BasePresenter extends BaseBase
{

    /** @var TokenValidator @inject */
    public $validator;

    /** @var string */
    protected $buffer;

    /**
     * Common cron method
     *
     * @throws ForbiddenRequestException
     */
    protected function startup()
    {
        parent::startup();

        if (!$this->validator->validate()) {
            throw new ForbiddenRequestException();
        }
    }

    /**
     * No rendering
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->flush();
    }

    /**
     * @param string $message
     */
    protected function output($message)
    {
        $this->buffer .= "$message\n";
    }

    /**
     * Send buffer and exit program
     */
    protected function flush()
    {
        $this->sendResponse(new TextResponse($this->buffer));
        $this->terminate();
    }

}
