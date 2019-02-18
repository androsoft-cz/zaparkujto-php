<?php

namespace App\Model\Routing;

use App\Model\Exceptions\Logical\InvalidStateException;
use App\Model\Orm\Organizations\OrganizationsRepository;
use Nette\Application\Routers\Route;

final class OrganizationRouteFactory
{

    /** @var OrganizationsRepository */
    private $organizations;

    /** @var LanguageRouteFactory */
    private $languageRouteFactory;

    /**
     * @param OrganizationsRepository $organizations
     * @param LanguageRouteFactory $languageRouteFactory
     */
    public function __construct(OrganizationsRepository $organizations, LanguageRouteFactory $languageRouteFactory)
    {
        $this->organizations = $organizations;
        $this->languageRouteFactory = $languageRouteFactory;
    }

    /**
     * @param string $mask
     * @param array $meta
     * @param int $flags
     * @param string $prefix
     * @return Route
     */
    public function create($mask, array $meta, $flags = 0, $prefix = '')
    {
        if (count($meta) == 2) {
            $metadata = [
                'presenter' => $meta[0],
                'action' => $meta[1],
            ];
        } else {
            throw new InvalidStateException('Invalid metadata');
        }

        $metadata['organization'] = [
            Route::FILTER_IN => function ($slug) {
                $organization = $this->organizations->getBy(['slug' => $slug]);

                return $organization ? $organization->id : NULL;
            },
            Route::FILTER_OUT => function ($id) {
                $organization = $this->organizations->getById($id);

                return $organization ? $organization->slug : NULL;
            },
        ];

        // Prepend locale
        $mask = $this->languageRouteFactory->createMask($mask);

        // Create route
        return new Route($prefix . $mask, $metadata, $flags);
    }

}
