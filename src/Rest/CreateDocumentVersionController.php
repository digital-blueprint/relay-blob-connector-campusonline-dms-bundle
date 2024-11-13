<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateDocumentVersionController extends AbstractController
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
    public function __invoke(Request $request, string $uid): ?Document
    {
        $this->requireAuthentication();
        $this->authorizationService->denyAccessUnlessHasRoleUser();

        $name = $request->request->get('name');
        $documentType = $request->request->get('document_type');

        $uploadedFile = Common::getAndValidateUploadedFile($request, 'binary_content');

        $documentVersionMetadataArray = null;
        $documentVersionMetadata = $request->request->get('metadata');
        if ($documentVersionMetadata !== null) {
            try {
                $documentVersionMetadataArray = json_decode($documentVersionMetadata, true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new Error(Response::HTTP_BAD_REQUEST, 'The metadata of the document version is not valid JSON',
                    errorCode: 'RESOURCE_MALFORMED_MDATA', errorDetail: 'metadata');
            }
        }

        return $this->documentService->addDocumentVersion(
            $uid, $uploadedFile, $name, $documentVersionMetadataArray, $documentType);
    }
}
