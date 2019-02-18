<?php

namespace App\Model\Facade;

use App\Core\Config\Config;
use App\Model\Exceptions\Runtime\Reservation\TokenException;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\ReservationTokens\ReservationToken;
use App\Model\Orm\ReservationTokens\ReservationTokensRepository;
use Nette\Utils\DateTime;

abstract class BaseReservationFacade
{

    /** @var Config */
    protected $config;

    /** @var ReservationTokensRepository */
    protected $reservationTokensRepository;

    /** @var ReservationsRepository */
    protected $reservationsRepository;

    /**
     * @param Config $config
     * @param ReservationTokensRepository $reservationTokensRepository
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(
        Config $config,
        ReservationTokensRepository $reservationTokensRepository,
        ReservationsRepository $reservationsRepository
    )
    {
        $this->config = $config;
        $this->reservationTokensRepository = $reservationTokensRepository;
        $this->reservationsRepository = $reservationsRepository;
    }

    /**
     * @param string $token
     * @return ReservationToken
     * @throws TokenException
     */
    public function validateToken($token)
    {
        /** @var ReservationToken $token */
        $token = $this->reservationTokensRepository->getBy(['token' => $token]);

        if (!$token) {
            throw new TokenException(NULL, TokenException::NOT_FOUND);
        }

        if ($token->expiredAt && $token->expiredAt < new DateTime()) {
            throw new TokenException(NULL, TokenException::EXPIRED);
        }

        if ($token->appliedAt) {
            throw new TokenException(NULL, TokenException::APPLIED);
        }

        return $token;
    }

}
