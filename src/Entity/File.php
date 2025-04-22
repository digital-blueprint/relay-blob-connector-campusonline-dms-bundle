<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\FileProcessor;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\FileProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'BlobConnectorCampusonlineDmsFile',
    types: ['https://schema.org/Document'],
    operations: [
        new Get(
            uriTemplate: '/co-dms-api/api/files/{uid}',
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: FileProvider::class
        ),
        new Post(
            uriTemplate: '/co-dms-api/api/files',
            inputFormats: ['json' => 'application/json'],
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'fileType' => [
                                        'type' => 'string',
                                        'example' => 'pdf',
                                    ],
                                    'metaData' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                ],
                                'required' => ['fileType'],
                            ],
                        ],
                    ])
                )
            ),
            processor: FileProcessor::class
        ),
        new Put(
            uriTemplate: '/co-dms-api/api/files/{uid}',
            inputFormats: ['json' => 'application/json'],
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'fileType' => [
                                        'type' => 'string',
                                        'example' => 'pdf',
                                    ],
                                    'metaData' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                ],
                                'required' => ['fileType'],
                            ],
                        ],
                    ])
                )
            ),
            provider: FileProvider::class,
            processor: FileProcessor::class
        ),
        new Delete(
            uriTemplate: '/co-dms-api/api/files/{uid}',
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: FileProvider::class,
            processor: FileProcessor::class
        ),
    ],
    normalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsFile:output']],
    denormalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsFile:input']]
)]
class File
{
    #[ApiProperty(identifier: true)]
    #[Groups(['BlobConnectorCampusonlineDmsFile:output'])]
    private ?string $uid = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsFile:input'])]
    private ?string $fileType = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsFile:output', 'BlobConnectorCampusonlineDmsFile:input'])]
    private ?array $metaData = null;

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): void
    {
        $this->fileType = $fileType;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): void
    {
        $this->metaData = $metaData;
    }
}
