<?php

namespace App\Model\Mailing;

use App\Core\Mail\Config;
use App\Core\Template\ITemplateFactory;
use Nette\Application\UI\ITemplate;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class MailFactory
{

    /** @var Config */
    private $config;

    /** @var IMailer */
    private $mailer;

    /** @var ITemplateFactory */
    private $templateFactory;

    /**
     * @param Config $config
     * @param IMailer $mailer
     */
    public function __construct(
        Config $config,
        IMailer $mailer,
        ITemplateFactory $templateFactory
    )
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @return Message
     */
    public function createMessage()
    {
        $message = new Message();
        $message->setFrom('no-reply@zaparkujto.cz');

        return $message;
    }

    /**
     * @param string $file
     * @return ITemplate
     */
    public function createTemplate($file = NULL)
    {
        $template = $this->templateFactory->createMailTemplate();

        if ($file) {
            $template->setFile($this->config->getDir() . '/' . $file . '.latte');
        }

        return $template;
    }

    /**
     * @param Message $message
     */
    public function send(Message $message)
    {
        $this->mailer->send($message);
    }

}
