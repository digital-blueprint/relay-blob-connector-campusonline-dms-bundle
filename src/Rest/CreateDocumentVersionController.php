<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

        $uploadedFile = $request->files->get('content');
        Common::ensureUpdatedFileIsValid($uploadedFile);

        return $this->documentService->addDocumentVersion($uid, $uploadedFile);
    }
}
