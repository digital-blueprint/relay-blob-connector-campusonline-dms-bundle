<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\DocumentVersionInfo;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File as FileEntity;
use Dbp\Relay\BlobLibrary\Api\BlobApi;
use Dbp\Relay\BlobLibrary\Api\BlobApiError;
use Dbp\Relay\BlobLibrary\Api\BlobFile;
use Dbp\Relay\CoreBundle\Rest\Query\Pagination\Pagination;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Uid\Uuid;

class DocumentService
{
    private const DOCUMENT_VERSION_METADATA_TYPE = 'document_version'; // config value?
    private const DOCUMENT_VERSION_METADATA_METADATA_KEY = 'doc_version_metadata';
    private const DOCUMENT_METADATA_METADATA_KEY = 'doc_metadata';
    private const VERSION_NUMBER_METADATA_KEY = 'version';
    private const DOCUMENT_TYPE_METADATA_KEY = 'doc_type';

    private ?BlobApi $blobApi = null;

    public function __construct(
        #[Autowire(service: 'service_container')]
        private readonly ContainerInterface $container)
    {
    }

    /**
     * @throws BlobApiError
     */
    public function setConfig(array $config): void
    {
        $this->blobApi = BlobApi::createFromConfig($config, $this->container);
    }

    /**
     * For testing purposes.
     */
    public function getBlobApi(): BlobApi
    {
        return $this->blobApi;
    }

    /**
     * @throws \Exception
     */
    public function getDocument(string $uid): Document
    {
        $latestDocumentVersionBlobFile = null;
        try {
            /** @var BlobFile $documentVersionBlobFile */
            foreach ($this->getDocumentVersionBlobFileCollection($uid) as $documentVersionBlobFile) {
                if ($latestDocumentVersionBlobFile === null
                    || $documentVersionBlobFile->getDateCreated() > $latestDocumentVersionBlobFile->getDateCreated()) {
                    $latestDocumentVersionBlobFile = $documentVersionBlobFile;
                }
            }
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError, 'document', $uid);
        }

        if ($latestDocumentVersionBlobFile === null) {
            throw new Error(Response::HTTP_NOT_FOUND, 'document not found', 'RESOURCE_NOT_FOUND', $uid);
        }

        $metadata = $this->getMetadataFromBlobFile($latestDocumentVersionBlobFile);
        $documentVersionInfo = $this->createDocumentVersionInfoFromBlobFile($latestDocumentVersionBlobFile, $metadata);

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
            foreach ($this->getDocumentVersionBlobFileCollection($uid) as $documentVersionFileData) {
                $this->blobApi->removeFile($documentVersionFileData->getIdentifier());
            }
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError, 'document', $uid);
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
            $this->blobApi->removeFile($uid);
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError, 'document version', $uid);
        }
    }

    /**
     * @throws \Exception
     */
    public function getDocumentVersionInfo(string $uid): ?DocumentVersionInfo
    {
        try {
            $documentVersionFileData = $this->blobApi->getFile($uid);
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError, 'document version', $uid);
        }

        return $this->createDocumentVersionInfoFromBlobFile($documentVersionFileData,
            $this->getMetadataFromBlobFile($documentVersionFileData));
    }

    /**
     * @throws \Exception
     */
    public function getDocumentVersionBinaryFileResponse(string $uid): Response
    {
        try {
            $blobFileStream = $this->blobApi->getFileStream($uid);
            $fileStream = $blobFileStream->getFileStream();

            return new StreamedResponse(
                function () use ($fileStream) {
                    while (!$fileStream->eof()) {
                        echo $fileStream->read(2048);
                        flush();
                    }
                }, Response::HTTP_OK, [
                    'Content-Type' => $blobFileStream->getMimeType(),
                    'Content-Length' => (string) $blobFileStream->getFileSize(),
                    'Content-Disposition' => HeaderUtils::makeDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT, $blobFileStream->getFileName()),
                ]);
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError, 'document version', $uid);
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
            $metadataEncoded = json_encode($metadata, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT);
        } catch (\Exception $jsonException) {
            throw new \RuntimeException(sprintf('encoding file metadata failed: %s', $jsonException->getMessage()));
        }

        $blobFile = new BlobFile();
        $blobFile->setFile($uploadedFile);
        $blobFile->setFileName($name);
        $blobFile->setPrefix($document->getUid());
        $blobFile->setType(self::DOCUMENT_VERSION_METADATA_TYPE);
        $blobFile->setMetadata($metadataEncoded);

        try {
            $blobFile = $this->blobApi->addFile($blobFile);
        } catch (BlobApiError $blobApiError) {
            throw self::createException($blobApiError);
        }

        return $this->createDocumentVersionInfoFromBlobFile($blobFile, $metadata);
    }

    /**
     * @throws \Exception
     */
    private function createDocumentVersionInfoFromBlobFile(BlobFile $blobFile, array $metadata): DocumentVersionInfo
    {
        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid($blobFile->getIdentifier());
        $documentVersionInfo->setName($blobFile->getFileName());
        $documentVersionInfo->setVersionNumber($metadata[self::VERSION_NUMBER_METADATA_KEY]);
        $documentVersionInfo->setMediaType($blobFile->getMimeType());
        $documentVersionInfo->setSize($blobFile->getFileSize());
        $documentVersionInfo->setMetaData($metadata[self::DOCUMENT_VERSION_METADATA_METADATA_KEY] ?? []);

        return $documentVersionInfo;
    }

    private function getMetadataFromBlobFile(BlobFile $blobFile): array
    {
        try {
            return json_decode($blobFile->getMetadata(), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Exception $jsonException) {
            throw new \RuntimeException(sprintf('decoding file metadata failed: %s', $jsonException->getMessage()));
        }
    }

    /**
     * @throws BlobApiError
     */
    private function getDocumentVersionBlobFileCollection(string $uid): iterable
    {
        return Pagination::getAllResultsPageNumberBased(
            function (int $currentPageNumber, int $maxNumItemsPerPage) use ($uid) {
                return $this->blobApi->getFiles($currentPageNumber, $maxNumItemsPerPage, options: [BlobApi::PREFIX_OPTION => $uid]);
            }, 128);
    }

    private static function createException(BlobApiError $blobApiError, ?string $resourceType = null,
        ?string $resourceUid = null): Error
    {
        return $blobApiError->getErrorId() === BlobApiError::FILE_NOT_FOUND ?
            new Error(Response::HTTP_NOT_FOUND,
                ($resourceType ?? 'resource').' not found', 'RESOURCE_NOT_FOUND', $resourceUid) :
            new Error(Response::HTTP_INTERNAL_SERVER_ERROR,
                $blobApiError->getMessage(), $blobApiError->getErrorId(), $resourceUid, $blobApiError->getBlobErrorId());
    }
}
