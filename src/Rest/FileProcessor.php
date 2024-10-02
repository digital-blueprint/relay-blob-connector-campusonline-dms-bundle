<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProcessor;

class FileProcessor extends AbstractDataProcessor
{
    protected static string $identifierName = 'uid';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    protected function addItem(mixed $data, array $filters): File
    {
        assert($data instanceof File);

        return $this->documentService->addFile($data);
    }

    protected function replaceItem(mixed $identifier, mixed $data, mixed $previousData, array $filters): File
    {
        assert($data instanceof File);

        return $this->documentService->replaceFile($identifier, $data);
    }

    protected function isCurrentUserGrantedOperationAccess(int $operation): bool
    {
        return $this->authorizationService->hasRoleUser();
    }
}
