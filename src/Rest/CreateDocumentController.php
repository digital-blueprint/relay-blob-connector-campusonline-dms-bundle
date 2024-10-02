<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Helpers\Tools;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            throw new BadRequestHttpException('parameter \'name\' must not be empty');
        }
        $documentType = $request->request->get('documentType'); // TODO: is documentType required?
        $uploadedFile = $request->files->get('content');
        Common::ensureUpdatedFileIsValid($uploadedFile);

        $metaDataArray = null;
        $metaData = $request->request->get('metaData');
        if ($metaData !== null) {
            try {
                $metaDataArray = json_decode($metaData, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new BadRequestHttpException('parameter \'metaData\' is invalid json');
            }
        }

        $document = new Document();
        $document->setName($name);
        $document->setDocumentType($documentType);
        $document->setMetaData($metaDataArray);

        return $this->documentService->addDocument($document, $uploadedFile);
    }
}
