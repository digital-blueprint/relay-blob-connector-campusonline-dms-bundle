<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\DocumentVersionInfo;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProvider;

/**
 * @extends AbstractDataProvider<DocumentVersionInfo>
 */
class DocumentVersionInfoProvider extends AbstractDataProvider
{
    protected static string $identifierName = 'uid';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function getItemById(string $id, array $filters = [], array $options = []): ?DocumentVersionInfo
    {
        return $this->documentService->getDocumentVersionInfo($id);
    }

    protected function getPage(int $currentPageNumber, int $maxNumItemsPerPage, array $filters = [], array $options = []): array
    {
        throw new \RuntimeException('not available');
    }

    protected function isCurrentUserGrantedOperationAccess(int $operation): bool
    {
        return $this->authorizationService->hasRoleUser();
    }
}
