<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use ApiPlatform\Metadata\Operation;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Health;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\ApiPlatform\State\StateProviderInterface;

class HealthProvider implements StateProviderInterface
{
    public function __construct(private DocumentService $documentService, private AuthorizationService $authorizationService)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->authorizationService->denyAccessUnlessHasRoleUser();

        if ($this->documentService->isHealthy()) {
            return new Health();
        }
        throw new Error(503, 'The service is currently unavailable');
    }
}
