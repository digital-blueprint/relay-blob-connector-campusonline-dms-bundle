<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use Dbp\Relay\BlobBundle\TestUtils\TestEntityManager;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Service\DocumentService;
use Dbp\Relay\CoreBundle\TestUtils\AbstractApiTest;
use Dbp\Relay\CoreBundle\TestUtils\TestClient;

class HealthApiTest extends AbstractApiTest
{
    protected function setUp(): void
    {
        $this->testClient = new TestClient(self::createClient());
        $this->testClient->setUpUser(userAttributes: ['MAY_USE_CO_DMS_API' => true]);
        $this->testClient->getClient()->disableReboot();
        TestEntityManager::setUpBlobEntityManager($this->testClient->getContainer());
    }

    public function testHealthUp()
    {
        $response = $this->testClient->get('/co-dms-api/api/health', options: [
            'headers' => [
                'Accept' => 'application/json',
            ]]);
        $this->assertSame(200, $response->getStatusCode());
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
        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringStartsWith('application/problem+json', $response->getHeaders(false)['content-type'][0]);
        $data = json_decode($response->getContent(false), flags: JSON_THROW_ON_ERROR);
        $this->assertSame($data->status, 503);
        $this->assertSame($data->detail, 'The service is currently unavailable');
    }

    public function testNotAllowed()
    {
        $this->testClient->setUpUser(userAttributes: ['MAY_USE_CO_DMS_API' => false]);
        $response = $this->testClient->get('/co-dms-api/api/health', options: [
            'headers' => [
                'Accept' => 'application/json',
            ]]);
        $this->assertSame(403, $response->getStatusCode());
    }
}
