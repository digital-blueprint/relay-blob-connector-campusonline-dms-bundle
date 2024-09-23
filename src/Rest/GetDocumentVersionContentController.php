<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetDocumentVersionContentController extends AbstractController
{
    public function __invoke(Request $request, string $uid): Response
    {
        if ($uid === 'error') {
            throw new BadRequestException('something went wrong');
        }

        // TODO: get blob file with given version and return its content
        return new BinaryFileResponse(__DIR__.'/../TestData/text_file.txt', 200, ['Content-Type' => 'application/octet-stream']);
    }
}
