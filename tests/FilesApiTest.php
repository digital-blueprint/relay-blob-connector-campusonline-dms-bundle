<?php

declare(strict_types=1);

use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\CoreBundle\TestUtils\AbstractApiTest;
use Dbp\Relay\CoreBundle\TestUtils\TestClient;
use Symfony\Component\HttpFoundation\Response;

class FilesApiTest extends AbstractApiTest
{
    protected function setUp(): void
    {
        $this->testClient = new TestClient(self::createClient());
        $this->testClient->setUpUser(userAttributes: ['MAY_USE_CO_DMS_API' => true]);
        $this->testClient->getClient()->disableReboot();
        TestEntityManager::setUpBlobEntityManager($this->testClient->getContainer());
    }

    public function testCreateFile(): void
    {
        $response = $this->testClient->request('POST', '/co-dms-api/api/files', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'fileType' => 'TEST_FILE_TYPE',
                'metaData' => [
                    'title' => 'Test Dossier',
                    'description' => 'Test Description',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $file = json_decode($response->getContent(false), true);

        $this->assertNotEmpty($file['uid']);
        $this->assertEquals([
            'title' => 'Test Dossier',
            'description' => 'Test Description',
        ], $file['metaData']);
    }

    public function testCreateFileMissingMetadata(): void
    {
        $response = $this->testClient->request('POST', '/co-dms-api/api/files', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
            ],
        ]);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateFileEmptyMetadata(): void
    {
        $response = $this->testClient->request('POST', '/co-dms-api/api/files', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'fileType' => 'TEST_FILE_TYPE',
                'metaData' => [
                ],
            ],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $file = json_decode($response->getContent(false), false);
        $this->assertNotEmpty($file->uid);
        $this->assertIsObject($file->metaData);
    }

    public function testGetFile(): void
    {
        $response = $this->testClient->get('/co-dms-api/api/files/123456', options: [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $file = json_decode($response->getContent(false), true);

        $this->assertEquals('123456', $file['uid']);
        $this->assertArrayNotHasKey('metaData', $file);
    }

    public function testRemoveFile(): void
    {
        $response = $this->testClient->request('DELETE', '/co-dms-api/api/files/123456');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUpdateFile(): void
    {
        $createResponse = $this->testClient->request('POST', '/co-dms-api/api/files', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'fileType' => 'TEST_FILE_TYPE',
                'metaData' => [
                    'title' => 'Test Dossier',
                    'description' => 'Test Description',
                ],
            ],
        ]);

        $createdFile = json_decode($createResponse->getContent(false), true);
        $fileUid = $createdFile['uid'];

        $response = $this->testClient->request('PUT', '/co-dms-api/api/files/'.$fileUid, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'fileType' => 'TEST_FILE_TYPE',
                'metaData' => [
                    'title' => 'Updated Test Dossier',
                    'description' => 'Updated Test Description',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
