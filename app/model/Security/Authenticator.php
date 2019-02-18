<?php

namespace App\Model\Security;

use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use Nette\Http\Response;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;

/**
 * Users management.
 */
class Authenticator implements IAuthenticator
{

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    /** @var UsersRepository */
    private $users;

    /** @var OrganizationsRepository */
    private $organizations;

    /** @var Authorizator */
    private $acl;

    /**
     * @param \Kdyby\Translation\Translator
     * @param Authorizator $authorizator
     * @param UsersRepository $users
     * @param OrganizationsRepository $organizations
     */
    public function __construct(
        \Kdyby\Translation\Translator $translator,
        Authorizator $authorizator,
        UsersRepository $users,
        OrganizationsRepository $organizations
    )
    {
        $this->translator = $translator;
        $this->acl = $authorizator;
        $this->users = $users;
        $this->organizations = $organizations;
    }

    /**
     * Performs an authentication.
     *
     * @return Identity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        /** @var User $user */
        $user = $this->users->getBy(['username' => $username]);

        if (!$user) {
            throw new AuthenticationException($this->translator->translate('misc.authenticator.username'), Response::S403_FORBIDDEN);
        }

        if (!Passwords::verify($password, $user->password)) {
            throw new AuthenticationException($this->translator->translate('misc.authenticator.password'), Response::S403_FORBIDDEN);
        }

        $user->loggedAt = new DateTime();
        $this->users->persistAndFlush($user);

        // Format data to Identity
        $data = $user->toIdentity();

        // If role is ROOT and has not filled organization, pick first one
        if ($data['role'] === Authorizator::ROOT && !$data['organization']) {
            $data['organization'] = $this->organizations->findAll()
                ->orderBy('id')
                ->fetch()
                ->toArray($user::TO_ARRAY_RELATIONSHIP_AS_ID);
        }

        return new Identity(
            $user->id,
            $this->acl->getParentRoles($user->role),
            $data
        );
    }

}
