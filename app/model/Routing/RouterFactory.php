<?php

namespace App\Model\Routing;

use AdamStipak\RestRoute;
use Nette\Application\Routers\CliRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Router factory.
 */
final class RouterFactory
{
    /** @var OrganizationRouteFactory */
    private $organizationRouteFactory;

    /** @var LanguageRouteFactory */
    private $languageRouteFactory;


    /**
     * @param OrganizationRouteFactory $organizationRouteFactory
     * @param LanguageRouteFactory $languageRouteFactory
     */
    public function __construct(
        OrganizationRouteFactory $organizationRouteFactory,
        LanguageRouteFactory $languageRouteFactory
    )
    {
        $this->organizationRouteFactory = $organizationRouteFactory;
        $this->languageRouteFactory = $languageRouteFactory;
    }


    /**
     * @return RouteList
     */
    public function create()
    {
        return $this->createWeb();
    }

    /**
     * @return RouteList
     */
    protected function createCli()
    {
        $router = new RouteList();

        $router[] = $cli = new RouteList('Cli');
        $cli[] = new CliRouter(['action' => 'Cli:hi']);

        return $router;
    }

    /**
     * @return RouteList
     */
    protected function createWeb()
    {
        $router = new RouteList();

        // Admin =================================
        $router[] = $admin = new RouteList('Admin');
        $admin[] = $this->languageRouteFactory->create('admin/<presenter>/<action>', ['Home', 'default']);
        $admin[] = $this->languageRouteFactory->create('jobs/<action>[/<id>]', ['Jobs', 'default']);

        // Api ===================================
        $router[] = $api = new RestRoute('Api', 'json', TRUE);
        // Rpmapi ===================================
        $router[] = $rpmapi = new RestRoute('Rpmapi', 'json', TRUE);

        // Cron
        $router[] = $cron = new RouteList('Cron');
        $cron[] = new Route('cron/<presenter>/<action>', 'Cron:default', Route::ONE_WAY);

        // Driver =================================
        $router[] = $driver = new RouteList('Driver');
        $driver[] = $this->languageRouteFactory->create('<presenter>/<action>', ['Home', 'default']);
        $driver[] = $this->languageRouteFactory->create('<presenter>/<action>[/<id>]', ['Home', 'default']);

        return $router;
    }

}
