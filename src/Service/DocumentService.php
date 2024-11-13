<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service;

use Dbp\Relay\BlobBundle\Api\FileApi;
use Dbp\Relay\BlobBundle\Api\FileApiException;
use Dbp\Relay\BlobBundle\Entity\FileData;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\DocumentVersionInfo;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File as FileEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class DocumentService
{
    public const BUCKET_ID = 'campusonline-dms-bucket';

    private const DOCUMENT_VERSION_METADATA_TYPE = 'document_version'; // config value?
    private const DOCUMENT_VERSION_METADATA_METADATA_KEY = 'doc_version_metadata';
    private const DOCUMENT_METADATA_METADATA_KEY = 'doc_metadata';
    private const VERSION_NUMBER_METADATA_KEY = 'version';
    private const DOCUMENT_TYPE_METADATA_KEY = 'doc_type';

    public function __construct(private readonly FileApi $fileApi)
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
            throw self::createException($fileApiException, 'document', $uid);
        }

        if ($latestDocumentVersionFileData === null) {
            throw new Error(Response::HTTP_NOT_FOUND, 'document not found', 'RESOURCE_NOT_FOUND', $uid);
        }

        $metadata = $this->getMetadataFromFileData($latestDocumentVersionFileData);
        $documentVersionInfo = $this->createDocumentVersionInfoFromFileData($latestDocumentVersionFileData, $metadata);

        $document = new Document();
        $document->setUid($uid);
        $document->setLatestVersion($documentVersionInfo);
        $document->setMetaData($metadata[self::DOCUMENT_METADATA_METADATA_KEY] ?? null);

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function addDocument(Document $document, File $uploadedFile, string $name,
        ?array $documentVersionMetadata = null, ?string $documentType = null): Document
    {
        $document->setUid((string) Uuid::v7());
        $document->setLatestVersion($this->createDocumentVersion(
            $document, $uploadedFile, $name, $documentVersionMetadata, $documentType));

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function removeDocument(string $uid): void
    {
        try {
            foreach ($this->getDocumentVersionFileDataCollection($uid) as $documentVersionFileData) {
                $this->fileApi->removeFile($documentVersionFileData->getIdentifier());
            }
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException, 'document', $uid);
        }
    }

    /**
     * @throws \Exception
     */
    public function addDocumentVersion(string $documentUid, File $uploadedFile,
        string $name, ?array $documentVersionMetadata = null, ?string $documentType = null): ?Document
    {
        $document = $this->getDocument($documentUid);
        $document->setLatestVersion($this->createDocumentVersion($document, $uploadedFile, $name,
            $documentVersionMetadata, $documentType, $document->getLatestVersion()->getVersionNumber()));

        return $document;
    }

    /**
     * @throws \Exception
     */
    public function removeDocumentVersion(string $uid): void
    {
        try {
            $this->fileApi->removeFile($uid);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException, 'document version', $uid);
        }
    }

    /**
     * @throws \Exception
     */
    public function getDocumentVersionInfo(string $uid): ?DocumentVersionInfo
    {
        try {
            $documentVersionFileData = $this->fileApi->getFile($uid);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException, 'document version', $uid);
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
            throw self::createException($fileApiException, 'document version', $uid);
        }
    }

    public function getFile(string $uid): ?FileEntity
    {
        $file = new FileEntity();
        $file->setUid($uid);

        return $file;
    }

    public function addFile(FileEntity $file): FileEntity
    {
        $file->setUid((string) Uuid::v7());

        return $file;
    }

    public function replaceFile(string $uid, FileEntity $file): FileEntity
    {
        $file->setUid($uid);

        return $file;
    }

    public function removeFile(string $uid, FileEntity $file): void
    {
    }

    /**
     * @throws \Exception
     */
    private function createDocumentVersion(Document $document, File $uploadedFile, string $name,
        ?array $documentVersionMetadata = null, ?string $documentType = null, ?string $lastVersion = null): DocumentVersionInfo
    {
        $versionNumber = $lastVersion ? strval(intval($lastVersion) + 1) : '1';

        $metadata = [];
        $metadata[self::VERSION_NUMBER_METADATA_KEY] = $versionNumber;
        if ($document->getMetaData() !== null) {
            $metadata[self::DOCUMENT_METADATA_METADATA_KEY] = $document->getMetaData();
        }
        if ($documentVersionMetadata !== null) {
            $metadata[self::DOCUMENT_VERSION_METADATA_METADATA_KEY] = $documentVersionMetadata;
        }
        if ($documentType !== null) {
            $metadata[self::DOCUMENT_TYPE_METADATA_KEY] = $documentType;
        }

        try {
            $metadataEncoded = json_encode($metadata, JSON_THROW_ON_ERROR);
        } catch (\Exception $jsonException) {
            throw new \RuntimeException(sprintf('encoding file metadata failed: %s', $jsonException->getMessage()));
        }

        $fileData = new FileData();
        $fileData->setFile($uploadedFile);
        $fileData->setFileName($name);
        $fileData->setPrefix($document->getUid());
        $fileData->setType(self::DOCUMENT_VERSION_METADATA_TYPE);
        $fileData->setMetadata($metadataEncoded);
        $fileData->setBucketId(self::BUCKET_ID);

        try {
            $fileData = $this->fileApi->addFile($fileData);
        } catch (FileApiException $fileApiException) {
            throw self::createException($fileApiException);
        }

        return $this->createDocumentVersionInfoFromFileData($fileData, $metadata);
    }

    /**
     * @throws \Exception
     */
    private function createDocumentVersionInfoFromFileData(FileData $fileData, array $metadata): DocumentVersionInfo
    {
        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid($fileData->getIdentifier());
        $documentVersionInfo->setName($fileData->getFileName());
        $documentVersionInfo->setVersionNumber($metadata[self::VERSION_NUMBER_METADATA_KEY]);
        $documentVersionInfo->setMediaType($fileData->getMimeType());
        $documentVersionInfo->setSize($fileData->getFileSize());
        $documentVersionInfo->setMetaData($metadata[self::DOCUMENT_VERSION_METADATA_METADATA_KEY] ?? null);

        return $documentVersionInfo;
    }

    /**
     * @throws \Exception
     */
    private function getMetadataFromFileData(FileData $fileData): array
    {
        try {
            return json_decode($fileData->getMetadata(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Exception $jsonException) {
            throw new \RuntimeException(sprintf('decoding file metadata failed: %s', $jsonException->getMessage()));
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

    private static function createException(FileApiException $fileApiException, ?string $resourceType = null,
        ?string $resourceUid = null): Error
    {
        return $fileApiException->getCode() === FileApiException::FILE_NOT_FOUND ?
            new Error(Response::HTTP_NOT_FOUND,
                ($resourceType ?? 'resource').' not found', 'RESOURCE_NOT_FOUND', $resourceUid) :
            new Error(Response::HTTP_INTERNAL_SERVER_ERROR,
                $fileApiException->getMessage(), strval($fileApiException->getCode()), $resourceUid);
    }
}
