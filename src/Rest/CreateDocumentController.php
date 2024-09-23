<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Uid\Uuid;

class CreateDocumentController extends AbstractController
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    public function __invoke(Request $request): Document
    {
        if (!$this->authorizationService->isAuthenticated()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

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
