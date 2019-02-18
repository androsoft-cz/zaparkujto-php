<?php

namespace App\Model\Orm\ReservationTokens;

use App\Model\Orm\AbstractRepository;
use Nette\Utils\Random;

class ReservationTokensRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            ReservationToken::class,
        ];
    }

    /**
     * @return ReservationToken
     */
    public function createUniqueToken()
    {
        do {
            $hash = Random::generate(100, '0-9a-zA-Z');
            $record = $this->getBy(['token' => $hash]);
        } while ($record != NULL);

        $token = new ReservationToken();
        $token->token = $hash;

        return $token;
    }

}
