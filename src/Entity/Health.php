<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\HealthProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/co-dms-api/api/health',
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                responses: [
                    '200' => new Response(
                        description: 'The DMS is up and running.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['status'],
                                    'properties' => [
                                        'status' => [
                                            'type' => 'string',
                                            'description' => 'The readiness of the DMS. To be initialized with \'UP\' if DMS is up and running',
                                            'default' => 'UP',
                                            'example' => 'UP',
                                        ],
                                    ],
                                ],
                            ],
                        ])
                    ),
                ],
                summary: 'DMS Health Endpoint',
                description: 'This endpoint returns the health status of the DMS.',
            ),
            provider: HealthProvider::class
        ),
    ]
)]
class Health
{
    private string $status = 'UP';

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
