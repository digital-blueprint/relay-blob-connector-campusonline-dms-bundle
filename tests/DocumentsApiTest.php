<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\CoreBundle\TestUtils\AbstractApiTest;
use Dbp\Relay\CoreBundle\TestUtils\TestClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentsApiTest extends AbstractApiTest
{
    private const TEST_FILE_NAME = 'test.txt';
    private const TEST_FILE_PATH = __DIR__.'/'.self::TEST_FILE_NAME;
    private const TEST_DOCUMENT_TYPE = 'doc_type';

    protected function setUp(): void
    {
        $this->testClient = new TestClient(self::createClient());
        $this->testClient->setUpUser(userAttributes: ['MAY_USE_CO_DMS_API' => true]);
        $this->testClient->getClient()->disableReboot();
        TestEntityManager::setUpBlobEntityManager($this->testClient->getContainer());
    }

    public function testGetDocumentNotExist(): void
    {
        $response = $this->testClient->request('GET', '/co-dms-api/api/documents/nope', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(404, $data['status']);
        $this->assertArrayHasKey('diagnosticContext', $data);
        $this->assertSame('RESOURCE_NOT_FOUND', $data['diagnosticContext']['ERROR_CODE']);
        $this->assertSame('nope', $data['diagnosticContext']['RESOURCE_UID']);
    }

    public function testCreateDocument(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{"foo": "bar"}',
                    'doc_version_metadata' => '{"bar": "baz"}',
                ],
            ],
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $this->assertNotEmpty($documentUid);
        $this->assertEquals(['foo' => 'bar'], $document['metaData']);

        $documentVersionUid = $document['latestVersion']['uid'];
        $this->assertNotEmpty($documentVersionUid);
        $this->assertEquals(self::TEST_FILE_NAME, $document['latestVersion']['name']);
        $this->assertEquals('1', $document['latestVersion']['versionNumber']);
        $this->assertEquals('text/plain', $document['latestVersion']['mediaType']);
        $this->assertEquals($file->getSize(), $document['latestVersion']['size']);
        $this->assertEquals(['bar' => 'baz'], $document['latestVersion']['metaData']);

        $response = $this->testClient->get('/co-dms-api/api/documents/'.$documentUid, options: [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $this->assertEquals($documentUid, $document['uid']);
        $this->assertEquals(['foo' => 'bar'], $document['metaData']);
        $this->assertEquals($documentVersionUid, $document['latestVersion']['uid']);
        $this->assertEquals(self::TEST_FILE_NAME, $document['latestVersion']['name']);
        $this->assertEquals('1', $document['latestVersion']['versionNumber']);
        $this->assertEquals('text/plain', $document['latestVersion']['mediaType']);
        $this->assertEquals($file->getSize(), $document['latestVersion']['size']);
        $this->assertEquals(['bar' => 'baz'], $document['latestVersion']['metaData']);

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid.'/metadata',
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ]]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $documentVersion = json_decode($response->getContent(false), true);
        $this->assertNotEmpty($documentVersion['uid']);
        $this->assertEquals(self::TEST_FILE_NAME, $documentVersion['name']);
        $this->assertEquals('1', $documentVersion['versionNumber']);
        $this->assertEquals('text/plain', $documentVersion['mediaType']);
        $this->assertEquals($file->getSize(), $documentVersion['size']);
        $this->assertEquals(['bar' => 'baz'], $documentVersion['metaData']);

        /** @var \ApiPlatform\Symfony\Bundle\Test\Response $response */
        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid.'/content',
            options: [
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ]]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        assert($response->getKernelResponse() instanceof StreamedResponse);
        $this->assertEquals($file->getContent(), $response->getBrowserKitResponse()->getContent());
    }

    public function testGetDocumentVersionContent(): void
    {
        // Create a document first
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $documentVersionUid = $document['latestVersion']['uid'];

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid.'/content',
            options: [
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($file->getContent(), $response->getBrowserKitResponse()->getContent());
    }

    public function testGetDocumentVersionContentMissing(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $documentVersionUid = $document['latestVersion']['uid'];

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/nope/content',
            options: [
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ],
            ]
        );
        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('about:blank', $data['type']);
        $this->assertSame(404, $data['status']);
        $this->assertSame('document version not found', $data['detail']);
        $this->assertSame('RESOURCE_NOT_FOUND', $data['diagnosticContext']['ERROR_CODE']);
        $this->assertSame('nope', $data['diagnosticContext']['RESOURCE_UID']);

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/nope/versions/'.$documentVersionUid.'/content',
            options: [
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ],
            ]
        );
        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('about:blank', $data['type']);
        $this->assertSame(404, $data['status']);
        $this->assertSame('document version not found', $data['detail']);
        $this->assertSame('RESOURCE_NOT_FOUND', $data['diagnosticContext']['ERROR_CODE']);
        $this->assertSame($documentVersionUid, $data['diagnosticContext']['RESOURCE_UID']);
    }

    public function testCreateDocumentMissingParameters(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                ],
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateDocumentMissingFile(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                    'doc_version_metadata' => '{"bar": "baz"}',
                ],
            ],
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testGetDocumentVersionMetadata(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                    'doc_version_metadata' => '{"bar": "baz"}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $documentVersionUid = $document['latestVersion']['uid'];

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid.'/metadata',
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $documentVersion = json_decode($response->getContent(false), true);
        $this->assertEquals($documentVersionUid, $documentVersion['uid']);
        $this->assertEquals(self::TEST_FILE_NAME, $documentVersion['name']);
        $this->assertEquals('1', $documentVersion['versionNumber']);
        $this->assertEquals('text/plain', $documentVersion['mediaType']);
        $this->assertEquals($file->getSize(), $documentVersion['size']);
        $this->assertEquals(['bar' => 'baz'], $documentVersion['metaData']);
    }

    public function testGetDocumentVersionMetadataMissing(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                    'doc_version_metadata' => '{"bar": "baz"}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $documentVersionUid = $document['latestVersion']['uid'];

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/nope/metadata',
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('about:blank', $data['type']);
        $this->assertSame(404, $data['status']);
        $this->assertSame('document version not found', $data['detail']);
        $this->assertSame('RESOURCE_NOT_FOUND', $data['diagnosticContext']['ERROR_CODE']);
        $this->assertSame('nope', $data['diagnosticContext']['RESOURCE_UID']);

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/nope/versions/'.$documentVersionUid.'/metadata',
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertSame(404, $response->getStatusCode());
        $data = json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('about:blank', $data['type']);
        $this->assertSame(404, $data['status']);
        $this->assertSame('document version not found', $data['detail']);
        $this->assertSame('RESOURCE_NOT_FOUND', $data['diagnosticContext']['ERROR_CODE']);
        $this->assertSame($documentVersionUid, $data['diagnosticContext']['RESOURCE_UID']);
    }

    public function testDeleteDocumentVersion(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $documentVersionUid = $document['latestVersion']['uid'];

        // Test deleting document version
        $response = $this->testClient->request(
            'DELETE',
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid.'/versions/'.$documentVersionUid.'/metadata',
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid,
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateDocumentVersion(): void
    {
        // Create a document first
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];
        $firstVersionUid = $document['latestVersion']['uid'];

        // Create a new version
        $newFile = new UploadedFile(self::TEST_FILE_PATH, 'new_version.txt');
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents/'.$documentUid.'/version', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $newFile,
                ],
                'parameters' => [
                    'name' => 'new_version.txt',
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{"version": "42"}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $documentVersion = json_decode($response->getContent(false), true);
        $this->assertNotEmpty($documentVersion['uid']);
        $latestVersion = $documentVersion['latestVersion'];
        $this->assertNotEquals($firstVersionUid, $documentVersion['uid']);
        $this->assertEquals('new_version.txt', $latestVersion['name']);
        $this->assertEquals('2', $latestVersion['versionNumber']);
        $this->assertEquals('text/plain', $latestVersion['mediaType']);
        $this->assertEquals($newFile->getSize(), $latestVersion['size']);
        $this->assertEquals(['version' => '42'], $latestVersion['metaData']);
    }

    public function testDeleteDocument(): void
    {
        $file = new UploadedFile(self::TEST_FILE_PATH, self::TEST_FILE_NAME);
        $response = $this->testClient->request('POST', '/co-dms-api/api/documents', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ],
            'extra' => [
                'files' => [
                    'binary_content' => $file,
                ],
                'parameters' => [
                    'name' => self::TEST_FILE_NAME,
                    'document_type' => self::TEST_DOCUMENT_TYPE,
                    'metadata' => '{}',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $document = json_decode($response->getContent(false), true);
        $documentUid = $document['uid'];

        $response = $this->testClient->request(
            'DELETE',
            '/co-dms-api/api/documents/'.$documentUid
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->testClient->get(
            '/co-dms-api/api/documents/'.$documentUid,
            options: [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
