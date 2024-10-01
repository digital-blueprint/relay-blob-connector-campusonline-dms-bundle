<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateDocumentController extends AbstractController
{
    use CustomControllerTrait;

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    public function __invoke(Request $request): Document
    {
        $this->requireAuthentication();

        $name = $request->request->get('name'); // TODO: validate name
        $documentType = $request->request->get('documentType'); // TODO: validate document type
        $uploadedFile = $request->files->get('content'); // TODO: validate uploaded file

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
        $document->setName($name);
        $document->setDocumentType($documentType);
        $document->setMetaData($metaDataArray);

        return $this->documentService->addDocument($document, $uploadedFile);
    }
}
