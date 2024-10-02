<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\DependencyInjection\Configuration;
use Dbp\Relay\CoreBundle\Authorization\AbstractAuthorizationService;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationService extends AbstractAuthorizationService
{
    public function denyAccessUnlessHasRoleUser(): void
    {
        if (!$this->hasRoleUser()) {
            throw ApiError::withDetails(Response::HTTP_FORBIDDEN);
        }
    }

    public function hasRoleUser(): bool
    {
        return $this->isGranted(Configuration::ROLE_USER);
    }
}
