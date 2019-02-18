<?php

namespace App\Modules\Cron;

use App\Model\Cron\HttpConsoleFactory;
use App\Model\Cron\TemplateOutput;
use Symfony\Component\Console\Input\ArrayInput;

final class CronPresenter extends BasePresenter
{

    /** @var HttpConsoleFactory @inject */
    public $httpConsoleFactory;

    /**
     * Common presenter method
     */
    public function startup()
    {
        parent::startup();

        // Create http wrapper for console
        $httpConsole = $this->httpConsoleFactory->create();

        // Get command or set default "list" command
        $command = $this->getParameter('command', 'list');

        $input = new ArrayInput(['command' => $command]);
        $output = new TemplateOutput();

        // Fire commands
        $httpConsole->doRun($input, $output);
    }

}
