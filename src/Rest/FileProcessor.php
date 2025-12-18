<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProcessor;
use Symfony\Component\HttpFoundation\Response;

class FileProcessor extends AbstractDataProcessor
{
    protected static string $identifierName = 'uid';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
        parent::__construct();
    }

    protected function addItem(mixed $data, array $filters): File
    {
        assert($data instanceof File);

        return $this->documentService->addFile($data);
    }

    protected function replaceItem(mixed $identifier, mixed $data, mixed $previousData, array $filters): Response
    {
        assert($data instanceof File);

        $this->documentService->replaceFile($identifier, $data);

        return new Response(status: 204); // API spec defines 204 'no-content' for success
    }

    protected function removeItem(mixed $identifier, mixed $data, array $filters): void
    {
        assert($data instanceof File);

        $this->documentService->removeFile($identifier, $data);
    }

    protected function isCurrentUserGrantedOperationAccess(int $operation): bool
    {
        return $this->authorizationService->hasRoleUser();
    }
}
