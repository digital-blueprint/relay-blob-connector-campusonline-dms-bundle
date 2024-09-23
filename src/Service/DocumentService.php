<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service;

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\DocumentVersionInfo;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\File;
use Symfony\Component\Uid\Uuid;

class DocumentService
{
    public function setConfig(array $config): void
    {
    }

    public function getDocument(string $uid): ?Document
    {
        $document = new Document();
        $document->setUid($uid);

        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid((string) Uuid::v7());

        $documentVersionInfo->setVersion('1');
        $documentVersionInfo->setMediaType('application/octet-stream');
        $documentVersionInfo->setSize(0);

        $document->setLatestContent($documentVersionInfo);

        // TODO:
        // * get blob file data of all versions by document uid
        // * get name. document type, amd metadata from file (meta) data
        // * write latest version info to 'latestContent'

        return $document;
    }

    public function addDocument(Document $document): Document
    {
        $document->setLatestContent($this->getDocumentVersionInfo((string) Uuid::v7()));

        return $document;
    }

    public function addDocumentVersion(string $documentUid, string $fileContent): ?Document
    {
        $document = $this->getDocument($documentUid);
        if (!$document) {
            return null;
        }

        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid((string) Uuid::v7());

        $versionNumber = intval($document->getLatestContent()->getVersion());
        $documentVersionInfo->setVersion(strval(++$versionNumber));

        $documentVersionInfo->setMediaType('application/octet-stream');
        $documentVersionInfo->setSize(strlen($fileContent));

        $document->setLatestContent($documentVersionInfo);

        return $document;
    }

    public function getDocumentVersionInfo(string $uid): ?DocumentVersionInfo
    {
        $documentVersionInfo = new DocumentVersionInfo();
        $documentVersionInfo->setUid($uid);
        $documentVersionInfo->setVersion('1');
        $documentVersionInfo->setMediaType('application/octet-stream');
        $documentVersionInfo->setSize(0);

        // TODO:
        // * get file data by version uid (=== file data uid?)
        // * get version info from file (meta) data

        return $documentVersionInfo;
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
}
