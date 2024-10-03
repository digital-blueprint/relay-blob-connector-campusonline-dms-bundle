<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service;

use Dbp\Relay\BlobBundle\Api\FileApi;
use Dbp\Relay\BlobBundle\Api\FileApiException;
use Dbp\Relay\BlobBundle\Entity\FileData;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\DocumentVersionInfo;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class DocumentService
{
    private const DOCUMENT_VERSION_METADATA_TYPE = 'document_version'; // config value?
    private const BUCKET_ID = 'campusonline-dms-bucket';

    public function __construct(private readonly FileApi $fileApi)
    {
    }

    public function setConfig(array $config): void
    {
    }

    /**
     * @throws \Exception
     */
    public function getDocument(string $uid): Document
    {
        $latestDocumentVersionFileData = null;
        try {
            foreach ($this->getDocumentVersionFileDataCollection($uid) as $documentVersionFileData) {
                if ($latestDocumentVersionFileData === null
                    || $documentVersionFileData->getDateCreated() > $latestDocumentVersionFileData->getDateCreated()) {
                    $latestDocumentVersionFileData = $documentVersionFileData;
                }
            }
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }

        if ($latestDocumentVersionFileData === null) {
            throw new NotFoundHttpException();
        }

        $metadata = $this->getMetadataFromFileData($latestDocumentVersionFileData);
        $documentVersionInfo = $this->createDocumentVersionInfoFromFileData($latestDocumentVersionFileData, $metadata);

        $document = new Document();
        $document->setUid($uid);
        $document->setLatestContent($documentVersionInfo);
        $document->setName($latestDocumentVersionFileData->getFileName());
        $document->setMetaData($metadata['metadata']);
        $document->setDocumentType($metadata['document_type']);

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function addDocument(Document $document, UploadedFile $uploadedFile): Document
    {
        $document->setUid((string) Uuid::v7());
        $document->setLatestContent($this->createDocumentVersion($document, $uploadedFile));

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function removeDocument(string $uid, Document $document): void
    {
        try {
            foreach ($this->getDocumentVersionFileDataCollection($uid) as $documentVersionFileData) {
                $this->fileApi->removeFile($documentVersionFileData->getIdentifier());
            }
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }
    }

    /**
     * @throws \Exception
     */
    public function addDocumentVersion(string $documentUid, UploadedFile $uploadedFile): ?Document
    {
        $document = $this->getDocument($documentUid);
        $document->setLatestContent($this->createDocumentVersion($document, $uploadedFile, $document->getLatestContent()->getVersion()));

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function getDocumentVersionInfo(string $uid): ?DocumentVersionInfo
    {
        try {
            $documentVersionFileData = $this->fileApi->getFile($uid);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }

        return $this->createDocumentVersionInfoFromFileData($documentVersionFileData,
            $this->getMetadataFromFileData($documentVersionFileData));
    }

    /**
     * @throws \Exception
     */
    public function getDocumentVersionBinaryFileResponse(string $uid): Response
    {
        try {
            return $this->fileApi->getBinaryFileResponse($uid);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }
    }

    public function getFile(string $uid): ?File
    {
        $file = new File();
        $file->setUid($uid);
        $file->setFileType('pdf');
        $file->setMetaData(['foo' => 'bar']);

        return $file;
    }

    public function addFile(File $file): File
    {
        $file->setUid((string) Uuid::v7());

        return $file;
    }

    public function replaceFile(string $uid, File $file): File
    {
        $file->setUid($uid);

        return $file;
    }

    /**
     * @throws \Exception
     */
    private function createDocumentVersion(Document $document, UploadedFile $uploadedFile, ?string $lastVersion = null): DocumentVersionInfo
    {
        $versionNumber = $lastVersion ? strval(intval($lastVersion) + 1) : '1';

        $metadata = [];
        $metadata['version'] = $versionNumber;
        if ($document->getDocumentType() !== null) {
            $metadata['document_type'] = $document->getDocumentType();
        }
        if ($document->getMetaData() !== null) {
            $metadata['metadata'] = $document->getMetaData();
        }

        try {
            $metadata = json_encode($metadata, JSON_THROW_ON_ERROR);
        } catch (\Exception $jsonException) {
            throw new \Exception($jsonException->getMessage(), 0, $jsonException);
        }

        $fileData = new FileData();
        $fileData->setFile($uploadedFile);
        $fileData->setFileName($document->getName());
        $fileData->setPrefix($document->getUid());
        $fileData->setType(self::DOCUMENT_VERSION_METADATA_TYPE);
        $fileData->setMetadata($metadata);

        try {
            $fileData = $this->fileApi->addFile($fileData, self::BUCKET_ID);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }

        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid($fileData->getIdentifier());
        $documentVersionInfo->setVersion($versionNumber);
        $documentVersionInfo->setMediaType($fileData->getMimeType());
        $documentVersionInfo->setSize($fileData->getFileSize());

        return $documentVersionInfo;
    }

    /**
     * @throws \Exception
     */
    private function createDocumentVersionInfoFromFileData(FileData $fileData, array $metadata): DocumentVersionInfo
    {
        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid($fileData->getIdentifier());
        $documentVersionInfo->setVersion($metadata['version']);
        $documentVersionInfo->setMediaType($fileData->getMimeType());
        $documentVersionInfo->setSize($fileData->getFileSize());

        return $documentVersionInfo;
    }

    /**
     * @throws \Exception
     */
    private function getMetadataFromFileData(FileData $fileData): array
    {
        try {
            return json_decode($fileData->getMetadata(), true, JSON_THROW_ON_ERROR);
        } catch (\Exception $jsonException) {
            throw new \Exception('metadata is invalid JSON', 0, $jsonException);
        }
    }

    /**
     * @return FileData[]
     *
     * @throws FileApiException
     */
    private function getDocumentVersionFileDataCollection(string $uid): array
    {
        return $this->fileApi->getFiles(self::BUCKET_ID, [FileApi::PREFIX_OPTION => $uid]);
    }

    private static function createException(FileApiException $fileApiException): \Exception
    {
        return $fileApiException->getCode() === FileApiException::FILE_NOT_FOUND ?
            new NotFoundHttpException() :
            new \Exception($fileApiException->getMessage(), $fileApiException->getCode(), $fileApiException);
    }
}
