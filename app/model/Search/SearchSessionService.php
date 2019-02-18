<?php

namespace App\Model\Search;

use Nette\Http\Session;
use Nette\Http\SessionSection;

final class SearchSessionService
{

    // Data keys
    const SEARCH = 'search';
    const NAVIGATOR = 'navigator';

    /** @var SessionSection */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session->getSection('search');
        $this->session->setExpiration(0);
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function store($key, $data)
    {
        $this->session->offsetSet($key, $data);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function restore($key)
    {
        return $this->session->offsetGet($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->session->offsetExists($key);
    }

}
