<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Symfony\Component\Filesystem\Filesystem;
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
            // look into form parameters if the file was sent in the form of a binary string parameter,
            // i.e. without the 'filename' directive in the 'content-disposition' header, which CO currently does.
            $binaryContent = $request->request->get($fileParameterName);
            if ($binaryContent === null) {
                throw new Error(Response::HTTP_BAD_REQUEST, 'parameter \'binary_content\' must not be empty',
                    errorCode: 'REQUIRED_PARAMETER_MISSING', errorDetail: 'binary_content');
            }
            $filesystem = new Filesystem();
            $tempFilePath = $filesystem->tempnam('/tmp', 'php');
            file_put_contents($tempFilePath, $binaryContent);
            $uploadedFile = new File($tempFilePath);
        }

        if ($uploadedFile === null) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'Parameter \''.$fileParameterName.'\' is required',
                errorCode: 'REQUIRED_PARAMETER_MISSING', errorDetail: $fileParameterName);
        }
        if ($uploadedFile instanceof File === false) {
            throw new Error(Response::HTTP_BAD_REQUEST, 'Parameter \''.$fileParameterName.'\' must be a file stream',
                errorCode: 'PARAMETER_TYPE_INVALID', errorDetail: $fileParameterName);
        }
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_OK) {
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
