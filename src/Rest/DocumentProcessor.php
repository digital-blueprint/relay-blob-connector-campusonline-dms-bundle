<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProcessor;

class DocumentProcessor extends AbstractDataProcessor
{
    private DocumentService $documentService;

    public function __construct(DocumentService $placeService)
    {
        $this->documentService = $placeService;
    }

    protected function addItem(mixed $data, array $filters): mixed
    {
        assert($data instanceof Document);
        dump($data);

        $data->setUid('42');

        return $this->documentService->addDocument($data);
    }

    protected function removeItem($identifier, $data, array $filters): void
    {
        assert($data instanceof Document);

        $this->documentService->removeDocument($data);
    }

    protected function isUserGrantedOperationAccess(int $operation): bool
    {
        return $this->isAuthenticated();
    }
}
