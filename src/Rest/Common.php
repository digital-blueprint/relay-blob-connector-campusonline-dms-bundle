<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Common
{
    public static function ensureUpdatedFileIsValid(mixed $uploadedFile): void
    {
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('content is required');
        }
        if ($uploadedFile instanceof UploadedFile === false) {
            throw new \RuntimeException('uploaded file is not an instance of UploadedFile as expected');
        }
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException(sprintf('file upload failed: %d', $uploadedFile->getError()));
        }
        if ($uploadedFile->getSize() === 0) {
            throw new BadRequestHttpException('uploaded file must not be empty');
        }
    }
}
