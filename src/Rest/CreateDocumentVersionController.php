<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        // create a fake UploadedFile since the file is required to be
        // uploaded as a binary stream by the API definition.
        // we use the 'test' flag of UploadedFile to skip the internal validation (bit hacky).
        // the alternative would be to change blob to work with File instead of UploadedFile
        $filesystem = new Filesystem();
        $tempFilePath = $filesystem->tempnam('/tmp', 'php');
        file_put_contents($tempFilePath, $request->getContent(false));
        $uploadedFile = new UploadedFile($tempFilePath, 'unknown', null, null, true);

        Common::ensureUpdatedFileIsValid($uploadedFile);

        return $this->documentService->addDocumentVersion($uid, $uploadedFile);
    }
}
