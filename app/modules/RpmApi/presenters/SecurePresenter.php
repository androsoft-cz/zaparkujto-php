<?php

namespace App\Modules\Rpmapi;

use App\Model\Exceptions\ApiException;
use App\Model\OrganizationRequest;
use App\Model\Security\Authorizator;
use App\Modules;
use Nette\Http\Response;

abstract class SecurePresenter extends BasePresenter
{

    /** @var Authorizator @inject */
    public $authorizator;

    public $organizationId;

    /** @var OrganizationRequest @inject */
    public $organizationRequest;

    public function startup()
    {
        parent::startup(); // TODO: Change the autogenerated stub

        $this->authenticateByToken($this->query);
        $this->checkAclRequirements();
        $this->organizationRequest->setOrganization($this->organizationId);
    }

    /**
     * Checks ACL authorization
     */
    protected function checkAclRequirements()
    {
        $role = current($this->user->getRoles());
        $resource = $this->getName();

        if (!$this->authorizator->isAllowed($role, $resource)) {
            $this->sendError('forbidden access to resource', Response::S403_FORBIDDEN);
            //throw new ForbiddenRequestException("forbidden access to resource", Response::S403_FORBIDDEN);
        }
    }

    public function authenticateByToken(array $query)
    {
        $authorization = explode(' ', $this->getHttpRequest()->getHeader('Authorization'));
        if ($authorization[0] == 'Bearer') {
            $access_token = $authorization[1];
        }

        if (!isset($access_token)) {
            $access_token = $query['access_token'];
        }

        if (!isset($access_token)) {
            $this->sendError('access token is not set', Response::S403_FORBIDDEN);
            //throw new BadRequestException("access token is not set", Response::S400_BAD_REQUEST);
        }

        /** @var \Nette\Security\IIdentity $identity */
        $identity = $this->cache->load($access_token);
        if (!isset($identity)) {
            $this->sendError('invalid access token', Response::S403_FORBIDDEN);
            //throw new ForbiddenRequestException("invalid access token", Response::S403_FORBIDDEN);
        }

        try {
            $this->user->login($identity);
        } catch (AuthenticationException $e) {
            $this->sendError('icould not authenticate by access token', Response::S403_FORBIDDEN);
            //throw new ForbiddenRequestException("could not authenticate by access token", Response::S403_FORBIDDEN);
        }

        $this->organizationId = $identity->organization['id'];
        /*
        if (!isset($this->organizationId)) {
            $this->sendError('organization is not set', Response::S403_FORBIDDEN);
            //throw new ForbiddenRequestException("organization is not set", Response::S403_FORBIDDEN);
        }
        */
    }

}