<?php

namespace App\Core\Cron\Http;

use Nette\Http\Request;

final class TokenValidator
{

    /** @var Request */
    private $request;

    /** @var string */
    private $token;

    /**
     * @param Request $request
     * @param string $token
     */
    public function __construct(Request $request, $token)
    {
        $this->request = $request;
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return $this->request->getQuery('_token') == $this->token;
    }

}
