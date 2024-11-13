<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Helpers\Tools;
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

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Document
    {
        $this->requireAuthentication();
        $this->authorizationService->denyAccessUnlessHasRoleUser();

        $name = $request->request->get('name');
        if (Tools::isNullOrEmpty($name)) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'parameter \'name\' must not be empty',
                errorCode: 'REQUIRED_PARAMETER_MISSING', errorDetail: 'name');
        }

        $uploadedFile = Common::getAndValidateUploadedFile($request, 'binary_content');

        $metadata = $request->request->get('metadata');
        if ($metadata === null) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'parameter \'metadata\' must not be empty',
                errorCode: 'REQUIRED_PARAMETER_MISSING', errorDetail: 'metadata');
        }

        try {
            $metadataArray = json_decode($metadata, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'The metadata of the document is not valid JSON',
                errorCode: 'RESOURCE_MALFORMED_MDATA', errorDetail: 'metadata');
        }
        $documentType = $request->request->get('documentType');

        $document = new Document();
        $document->setMetaData($metadataArray);

        $documentVersionMetadataArray = null;
        $documentVersionMetadata = $request->request->get('doc_version_metadata');
        if ($documentVersionMetadata !== null) {
            try {
                $documentVersionMetadataArray = json_decode($documentVersionMetadata, true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new Error(Response::HTTP_BAD_REQUEST, 'The metadata of the document version is not valid JSON',
                    errorCode: 'RESOURCE_MALFORMED_MDATA', errorDetail: 'doc_version_metadata');
            }
        }

        return $this->documentService->addDocument($document, $uploadedFile, $name, $documentVersionMetadataArray, $documentType);
    }
}
