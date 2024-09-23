<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use ApiPlatform\Metadata\ApiResource;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class CreateDocumentController extends AbstractController
{
    private DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function __invoke(Request $request): Document
    {
        $name = $request->request->get('name'); // TODO: validate name
        $documentType = $request->request->get('documentType'); // TODO: validate document type
        $content = $request->request->get('content'); // TODO: validate content

        $metaDataArray = null;
        $metaData = $request->request->get('metaData'); // TODO: validate metadata
        if ($metaData !== null) {
            try {
                $metaDataArray = json_decode($metaData, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw ApiError::withDetails(Response::HTTP_BAD_REQUEST, 'field \'metaData\' is invalid json', 'TODO', ['metaData']);
            }
        }

        $document = new Document();
        $document->setUid((string) Uuid::v7());
        $document->setName($name);
        $document->setDocumentType($documentType);
        $document->setContent($content);
        $document->setMetaData($metaDataArray);

        return $this->documentService->addDocument($document);
    }
}
