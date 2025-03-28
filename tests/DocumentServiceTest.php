<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\BlobBundle\Api\FileApi;
use Dbp\Relay\BlobBundle\TestUtils\BlobTestUtils;
use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Document;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Symfony\Component\HttpFoundation\File\File;

class DocumentServiceTest extends ApiTestCase
{
    private const TEST_FILE_NAME = 'test.txt';
    private const TEST_FILE_2_NAME = 'test_patch.txt';
    protected ?DocumentService $documentService = null;
    protected ?TestEntityManager $blobTestEntityManager = null;
    private FileApi $fileApi;

    public static function getBLobTestConfig(): array
    {
        $testConfig = BlobTestUtils::getTestConfig();
        $testConfig['buckets'][0]['bucket_id'] = DocumentService::BUCKET_ID;
        $testConfig['buckets'][0]['additional_types'] = [
            ['document_version' => __DIR__.'/document_version.schema.json'],
        ];

        return $testConfig;
    }

    protected function setUp(): void
    {
        $this->setUpFileApi();
    }

    protected function setUpFileApi(): void
    {
        $this->blobTestEntityManager = new TestEntityManager(self::bootKernel()->getContainer());
        $this->fileApi = BlobTestUtils::createTestFileApi(
            $this->blobTestEntityManager->getEntityManager(),
            self::getBLobTestConfig()
        );
        $this->documentService = new DocumentService($this->fileApi);
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

        $res = $this->fileApi->getFile($document->getLatestVersion()->getUid());
        $this->assertNotNull($res->getFile());
        $this->assertSame($file->getContent(), $res->getFile()->getContent());
    }

    protected static function getInternalBucketId(): ?string
    {
        return BlobTestUtils::getTestConfig()['buckets'][0]['internal_bucket_id'] ?? null;
    }
}
