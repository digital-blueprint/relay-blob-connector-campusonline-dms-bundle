<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProvider;

/**
 * @extends AbstractDataProvider<Document>
 */
class DocumentProvider extends AbstractDataProvider
{
    protected static string $identifierName = 'uid';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    /**
     * @throws \Exception
     */
    protected function getItemById(string $id, array $filters = [], array $options = []): ?Document
    {
        if ($id === 'unhandled') {
            throw new \RuntimeException('unhandled error');
        } elseif ($id === 'apierror418') {
            throw new ApiError(418, 'no tea today');
        } elseif ($id === 'apierror500') {
            throw new ApiError(500, 'something went wrong');
        } elseif ($id === 'apierror500wd') {
            throw ApiError::withDetails(502, 'campusonline failed', 'campusonline-dms:campusonline-failed', ['foo' => 'bar']);
        } elseif ($id === 'apierror418wd') {
            throw ApiError::withDetails(418, 'no tea today', 'campusonline-dms:no-tea-today', ['no' => null]);
        }

        return $this->documentService->getDocument($id);
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
