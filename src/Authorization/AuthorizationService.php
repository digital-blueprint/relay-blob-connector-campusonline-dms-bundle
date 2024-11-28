<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\DependencyInjection\Configuration;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\CoreBundle\Authorization\AbstractAuthorizationService;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationService extends AbstractAuthorizationService
{
    /**
     * @throws Error
     */
    public function denyAccessUnlessHasRoleUser(): void
    {
        if (!$this->hasRoleUser()) {
            throw new Error(Response::HTTP_FORBIDDEN);
        }
    }

    public function hasRoleUser(): bool
    {
        return $this->isGrantedRole(Configuration::ROLE_USER);
    }
}
