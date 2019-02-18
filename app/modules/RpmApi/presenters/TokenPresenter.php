<?php

namespace App\Modules\Rpmapi;

use App\Model\Orm\ApiTokens\ApiToken;
use App\Model\Orm\ApiTokens\ApiTokensRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Security\Authorizator;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Utils\DateTime;

class TokenPresenter extends BasePresenter
{

    /** @var ApiTokensRepository $apiTokensRepository @inject */
    public $apiTokensRepository;

    /** @var UsersRepository $usersRepository @inject */
    public $usersRepository;

    /** @var Authorizator $acl @inject */
    public $acl;

    //  public function actionRead($grant_type, $login, $password, $refresh_token, $query)
    public function actionCreate(array $query, $data)
    {
        $type = $this->getHttpRequest()->getHeader('Content-Type');

        //if (strpos($type, 'x-www-form-urlencoded') !== false) {
        if (strpos($type, 'application/json') !== FALSE) {
            $decoded_data = json_decode($data);
        } else {
            parse_str($data, $decoded_data);
        }

        if (isset($query['grant_type'])) {
            $decoded_data = $query;
            $grant_type = $query['grant_type'];
        };

        if (isset($decoded_data['grant_type'])) {
            $grant_type = $decoded_data['grant_type'];
        };

        if (!isset($grant_type)) {
            $this->sendError('grant_type is not set', Response::S400_BAD_REQUEST);
            //throw new Application\BadRequestException("grant_type is not set", Response::S400_BAD_REQUEST);
        }

        if ($grant_type == 'refresh_token') {
            $response = $this->authenticateByToken($decoded_data);
        }

        if ($grant_type == 'password') {
            $response = $this->authenticateByPassword($decoded_data);
        }

        if (isset($response)) {
            $this->sendResponse(new JsonResponse($response));
        } else {
            $this->sendError('error building response', Response::S400_BAD_REQUEST);
            //throw new Application\BadRequestException("error building response", Response::S400_BAD_REQUEST);
        }
    }

    public function authenticateByPassword(array $query)
    {
        if (isset($query['login']) && isset($query['password'])) {
            try {
                $login = $query['login'];
                $password = $query['password'];

                $this->user->login($login, $password);
                $user = $this->usersRepository->findById($this->user->id)->fetch();

                $accessToken = $this->generateAccessToken();
                $refreshToken = $this->generateRefreshToken();

                $newRefreshToken = new ApiToken();
                $newRefreshToken->type = ApiToken::TYPE_REFRESH;
                $newRefreshToken->createdAt = new DateTime();
                $newRefreshToken->user = $user;
                $newRefreshToken->expiredAt = new DateTime();
                $newRefreshToken->token = $refreshToken;
                $this->apiTokensRepository->persistAndFlush($newRefreshToken);
                $this->cache->save($accessToken, $this->user->identity, $this->cacheDependencies);

                $response = [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_in' => self::EXPIRE_TIME,
                ];

                return $response;
            } catch (AuthenticationException $e) {
                $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
                //throw new Application\BadRequestException($e->getMessage(), $e->getCode());
            }
        } else {
            $this->sendError('login or password is not set', Response::S400_BAD_REQUEST);
            //throw new Application\BadRequestException("login or password is not set", Response::S400_BAD_REQUEST);
        }
    }

    public function authenticateByToken($query)
    {
        if (isset($query['refresh_token'])) {
            $refresh_token = $query['refresh_token'];
        } else {
            $this->sendError('Refresh token is not set', Response::S400_BAD_REQUEST);
        }

        $identity = $this->cache->load($refresh_token); //zmenit za nacteni z databaze

        /** @var ApiToken $apiToken */
        $apiToken = $this->apiTokensRepository->findBy(['token' => $refresh_token])->fetch();

        if (!$apiToken) {
            $this->sendError('Refresh token is not valid', Response::S400_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $apiToken->user;

        $identity = new Identity(
            $user->id,
            $this->acl->getParentRoles($user->role),
            $user->toIdentity()
        );

        $accessToken = $this->generateAccessToken();
        $this->cache->save($accessToken, $identity, $this->cacheDependencies);

        $response = [
            'access_token' => $accessToken,
            'expires_in' => self::EXPIRE_TIME,
        ];

        return $response;
    }

    public function generateAccessToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function generateRefreshToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
