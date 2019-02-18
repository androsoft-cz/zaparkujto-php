<?php

namespace App\Model\Routing;

use App\Model\Exceptions\Logical\InvalidStateException;
use Nette\Application\Routers\Route;

final class LanguageRouteFactory
{

    /** @var string */
    private $defaultLanguage;

    /** @var array */
    private $languages;

    /**
     * @param string $defaultLanguage
     * @param array $languages
     */
    public function __construct($defaultLanguage, array $languages)
    {
        $this->defaultLanguage = $defaultLanguage;
        $this->languages = $languages;
    }

    /**
     * @param string $mask
     * @param array $meta
     * @param int $flags
     * @return Route
     */
    public function create($mask, array $meta, $flags = 0)
    {
        if (count($meta) == 2) {
            $metadata = [
                'presenter' => $meta[0],
                'action' => $meta[1],
            ];
        } else {
            throw new InvalidStateException('Invalid metadata');
        }

        // Create route
        return new Route($this->createMask($mask), $metadata, $flags);
    }

    /**
     * @param string $mask
     * @return string
     */
    public function createMask($mask)
    {
        // Build locale mask
        $locale = sprintf('[!<locale=%s %s>/]', $this->defaultLanguage, implode('|', $this->languages));

        // Prepend to origin mask
        $mask = $locale . $mask;

        return $mask;
    }

}
