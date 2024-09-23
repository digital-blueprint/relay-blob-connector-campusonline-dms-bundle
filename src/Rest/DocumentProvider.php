<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProvider;

/**
 * @extends AbstractDataProvider<Document>
 */
class DocumentProvider extends AbstractDataProvider
{
    protected static string $identifierName = 'uid';

    private DocumentService $documentService;

    public function __construct(DocumentService $placeService)
    {
        $this->documentService = $placeService;
    }

    protected function getItemById(string $id, array $filters = [], array $options = []): ?Document
    {
        return $this->documentService->getDocument($id);
    }

    protected function getPage(int $currentPageNumber, int $maxNumItemsPerPage, array $filters = [], array $options = []): array
    {
        throw new \RuntimeException('not available');
    }
}
