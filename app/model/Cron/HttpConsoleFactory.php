<?php

namespace App\Model\Cron;

use Kdyby\Console\Application;
use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\Container;

final class HttpConsoleFactory
{

    /** @var Container */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return HttpConsole
     */
    public function create()
    {
        /** @var Application $application */
        $application = $this->container->getByType(Application::class);

        // Find commands
        foreach ($this->container->findByTag(ConsoleExtension::COMMAND_TAG) as $service => $s) {
            $application->add($this->container->getService($service));
        }

        return new HttpConsole($application);
    }

}
