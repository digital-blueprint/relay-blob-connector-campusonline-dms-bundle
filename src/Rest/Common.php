<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Common
{
    /**
     * @throws Error
     */
    public static function getAndValidateUploadedFile(Request $request, string $fileParameterName): File
    {
        $uploadedFile = $request->files->get($fileParameterName);
        if ($uploadedFile === null) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'Parameter \''.$fileParameterName.'\' is required',
                errorCode: 'REQUIRED_PARAMETER_MISSING', errorDetail: $fileParameterName);
        }
        if ($uploadedFile instanceof UploadedFile === false) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'Parameter \''.$fileParameterName.'\' must be a file stream',
                errorCode: 'PARAMETER_TYPE_INVALID', errorDetail: $fileParameterName);
        }
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new Error(Response::HTTP_BAD_REQUEST, sprintf('file stream upload failed: %d', $uploadedFile->getError()),
                errorCode: 'FILE_UPLOAD_FAILED', errorDetail: $fileParameterName);
        }
        if ($uploadedFile->getSize() === 0) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'uploaded file stream must not be empty',
                errorCode: 'FILE_MUST_NOT_BE_EMPTY', errorDetail: $fileParameterName);
        }

        return $uploadedFile;
    }
}
