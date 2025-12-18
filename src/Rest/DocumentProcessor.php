<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\AbstractDataProcessor;

class DocumentProcessor extends AbstractDataProcessor
{
    protected static string $identifierName = 'uid';

    public function __construct(private readonly DocumentService $documentService)
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function removeItem(mixed $identifier, mixed $data, array $filters): void
    {
        assert($data instanceof Document);

        $this->documentService->removeDocument($identifier);
    }
}
