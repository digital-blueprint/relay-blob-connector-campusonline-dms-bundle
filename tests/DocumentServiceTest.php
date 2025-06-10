<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\BlobBundle\TestUtils\BlobTestUtils;
use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\BlobLibrary\Api\BlobApi;
use Dbp\Relay\BlobLibrary\Api\BlobApiError;
use Symfony\Component\HttpFoundation\File\File;

class DocumentServiceTest extends ApiTestCase
{
    private const TEST_FILE_NAME = 'test.txt';
    private const BUCKET_ID = 'document-bucket';
    protected ?DocumentService $documentService = null;
    protected ?TestEntityManager $blobTestEntityManager = null;
    private ?BlobApi $blobApi = null;

    public static function getBLobTestConfig(): array
    {
        $testConfig = BlobTestUtils::getTestConfig();
        $testConfig['buckets'][0]['bucket_id'] = self::BUCKET_ID;
        $testConfig['buckets'][0]['additional_types'] = [
            ['document_version' => __DIR__.'/document_version.schema.json'],
        ];

        return $testConfig;
    }

    /**
     * @throws BlobApiError
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDocumentService();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BlobTestUtils::tearDown();
    }

    /**
     * @throws BlobApiError
     */
    protected function setUpDocumentService(): void
    {
        $container = self::bootKernel()->getContainer();

        $this->blobTestEntityManager = new TestEntityManager($container);
        TestEntityManager::setUpBlobEntityManager($container);

        $this->documentService = new DocumentService($container);
        $this->documentService->setConfig(BlobApi::getCustomModeConfig(self::BUCKET_ID));
        $this->blobApi = $this->documentService->getBlobApi();
    }

    /**
     * @throws \Exception
     */
    public function testCreateDocument(): void
    {
        $document = new Document();
        $documentMetadata = ['foo' => 'bar'];
        $document->setMetadata($documentMetadata);

        $file = new File(__DIR__.'/'.self::TEST_FILE_NAME, true);
        $documentVersionMetadata = [
            'bar' => 'baz',
        ];
        $documentType = 'transcript_of_records';

        $document = $this->documentService->addDocument(
            $document, $file, self::TEST_FILE_NAME, $documentVersionMetadata, $documentType);

        $this->assertNotEmpty($document->getUid());
        $this->assertEquals($documentMetadata, $document->getMetaData());
        $this->assertNotEmpty($document->getLatestVersion()->getUid());
        $this->assertEquals(self::TEST_FILE_NAME, $document->getLatestVersion()->getName());
        $this->assertEquals($documentVersionMetadata, $document->getLatestVersion()->getMetadata());
        $this->assertEquals('1', $document->getLatestVersion()->getVersionNumber());
        $this->assertEquals($file->getSize(), $document->getLatestVersion()->getSize());
        $this->assertEquals($file->getMimeType(), $document->getLatestVersion()->getMediaType());

        $this->assertFileContentsEquals($document->getLatestVersion()->getUid(), $file->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testGetDocument(): void
    {
        $documentMetadata = ['foo' => 'bar'];
        $documentVersionMetadata = [
            'bar' => 'baz',
        ];
        $documentType = 'transcript_of_records';
        $file = new File(__DIR__.'/'.self::TEST_FILE_NAME, true);
        $document = $this->createTestDocument($documentMetadata, $documentVersionMetadata, $documentType);

        $document = $this->documentService->getDocument($document->getUid());

        $this->assertNotEmpty($document->getUid());
        $this->assertEquals($documentMetadata, $document->getMetaData());
        $this->assertNotEmpty($document->getLatestVersion()->getUid());
        $this->assertEquals(self::TEST_FILE_NAME, $document->getLatestVersion()->getName());
        $this->assertEquals($documentVersionMetadata, $document->getLatestVersion()->getMetadata());
        $this->assertEquals('1', $document->getLatestVersion()->getVersionNumber());
        $this->assertEquals($file->getSize(), $document->getLatestVersion()->getSize());
        $this->assertEquals($file->getMimeType(), $document->getLatestVersion()->getMediaType());

        $this->assertFileContentsEquals($document->getLatestVersion()->getUid(), $file->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testGetDocumentVersionInfo(): void
    {
        $documentVersionMetadata = [
            'bar' => 'baz',
        ];
        $file = new File(__DIR__.'/'.self::TEST_FILE_NAME, true);
        $document = $this->createTestDocument(documentVersionMetadata: $documentVersionMetadata);

        $documentVersionInfo = $this->documentService->getDocumentVersionInfo($document->getLatestVersion()->getUid());
        $this->assertNotEmpty($documentVersionInfo->getUid());
        $this->assertEquals(self::TEST_FILE_NAME, $documentVersionInfo->getName());
        $this->assertEquals($documentVersionMetadata, $documentVersionInfo->getMetadata());
        $this->assertEquals('1', $documentVersionInfo->getVersionNumber());
        $this->assertEquals($file->getSize(), $documentVersionInfo->getSize());
        $this->assertEquals($file->getMimeType(), $documentVersionInfo->getMediaType());

        $this->assertFileContentsEquals($documentVersionInfo->getUid(), $file->getContent());
    }

    /**
     * @throws \Exception
     */
    protected function createTestDocument(
        array $documentMetadata = ['foo' => 'bar'],
        array $documentVersionMetadata = ['bar' => 'baz'],
        string $documentType = 'transcript_of_records'): Document
    {
        $document = new Document();
        $document->setMetadata($documentMetadata);
        $file = new File(__DIR__.'/'.self::TEST_FILE_NAME, true);

        return $this->documentService->addDocument(
            $document, $file, self::TEST_FILE_NAME, $documentVersionMetadata, $documentType);
    }

    /**
     * @throws BlobApiError
     */
    protected function assertFileContentsEquals(string $identifier, string $expectedContent): void
    {
        $this->assertEquals($expectedContent,
            $this->blobApi->getFileStream($identifier)->getFileStream()->getContents());
    }

    protected static function getInternalBucketId(): ?string
    {
        return BlobTestUtils::getTestConfig()['buckets'][0]['internal_bucket_id'] ?? null;
    }
}
