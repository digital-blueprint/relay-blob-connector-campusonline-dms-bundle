<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\TestUtils\AbstractApiTest;
use Dbp\Relay\CoreBundle\TestUtils\TestClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiTest extends AbstractApiTest
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
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            dump(json_decode($response->getContent(false), true, flags: JSON_THROW_ON_ERROR));
        }
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
            '/co-dms-api/api/documents/version/'.$documentVersionUid.'/metadata',
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
            '/co-dms-api/api/documents/version/'.$documentVersionUid.'/content',
            options: [
                'headers' => [
                    'Accept' => 'application/octet-stream',
                ]]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        assert($response->getKernelResponse() instanceof StreamedResponse);
        $this->assertEquals($file->getContent(), $response->getBrowserKitResponse()->getContent());
    }

    public function testHealthUp()
    {
        $response = $this->testClient->get('/co-dms-api/api/health', options: [
            'headers' => [
                'Accept' => 'application/json',
            ]]);
        $data = json_decode($response->getContent(), flags: JSON_THROW_ON_ERROR);
        $this->assertStringStartsWith('application/json', $response->getHeaders()['content-type'][0]);
        $this->assertSame($data->status, 'UP');

        $service = self::getContainer()->get(DocumentService::class);
        $service->setIsHealthy(false);
    }

    public function testHealthDown()
    {
        $service = self::getContainer()->get(DocumentService::class);
        $service->setIsHealthy(false);

        $response = $this->testClient->get('/co-dms-api/api/health', options: [
            'headers' => [
                'Accept' => 'application/json',
            ]]);
        $this->assertStringStartsWith('application/problem+json', $response->getHeaders(false)['content-type'][0]);
        $data = json_decode($response->getContent(false), flags: JSON_THROW_ON_ERROR);
        $this->assertSame($data->status, 503);
        $this->assertSame($data->detail, 'The service is currently unavailable');
    }
}
