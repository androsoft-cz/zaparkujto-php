<?php

namespace App\Model\Cron;

use Kdyby\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HttpConsole
{

    /** @var Application */
    private $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->application->doRun($input, $output);
    }

}
