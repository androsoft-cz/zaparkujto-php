<?php

namespace App\Modules\Rpmapi;

use App\Model\Exceptions\RuntimeException;
use App\Model\Mailing\MailService;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Search\SearchArgs;
use Nette\DI\Container;
use Nette\Http\Response;
use Nette\Utils\DateTime;

class PasswordResetPresenter extends BasePresenter
{

    /** @var SearchArgs @inject */
    public $search;

    /** @var UsersRepository @inject */
    public $usersRepository;

    /** @var MailService @inject */
    public $mailService;

    /** @var Container @inject */
    public $container;


    /**
     * @param mixed $data
     */
    public function actionCreate($data)
    {

        $d = json_decode($data);

        if (!$d) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (!isset($d->email)) {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        if (!isset($d->language)) {
            $locale = $this->container->getParameters()['translation']['default'];
        } elseif (in_array($d->language, $this->container->getParameters()['translation']['whitelist'])) {
            $locale = $d->language;
        } else {
            $this->sendError('Request body is not in our JSON format. Call me, baby!', Response::S400_BAD_REQUEST);
        }

        try {
            $user = $this->usersRepository->getByUsername($d->email);

            if (!$user) {
                $exception = (new RuntimeException('User not found.'))->setApiMessage('itemNotFound');
                $this->sendErrorByException($exception);
            }

            $resetToken = bin2hex(random_bytes(20));

            $user->passwordResetToken = $resetToken;
            $user->passwordResetTokenGeneratedAt = new DateTime();
            $this->usersRepository->persistAndFlush($user);

            $this->mailService->sendPasswordReset($resetToken, $user, $locale);

            $results = [
                'code' => 200,
                'result' => 'email sent',
            ];

            $this->sendApiResponse($results);

        } catch (\ErrorException $e) {
            $this->sendError('Error while reseting password!' . $e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
