<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Authorization\AuthorizationService;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GetDocumentVersionContentController extends AbstractController
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AuthorizationService $authorizationService)
    {
    }

    public function __invoke(Request $request, string $uid): Response
    {
        if (!$this->authorizationService->isAuthenticated()) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        if ($uid === 'error') {
            throw new BadRequestException('something went wrong');
        }

        // TODO: get blob file with given version and return its content
        return new BinaryFileResponse(__DIR__.'/../TestData/text_file.txt', 200, ['Content-Type' => 'application/octet-stream']);
    }
}
