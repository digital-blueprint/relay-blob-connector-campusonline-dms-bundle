<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CreateDocumentVersionController extends AbstractController
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    public function __invoke(Request $request, string $uid): ?Document
    {
        if (!$this->authorizationService->isAuthenticated()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        $content = $request->get('content');

        return $this->documentService->addDocumentVersion($uid, $content);
    }
}
