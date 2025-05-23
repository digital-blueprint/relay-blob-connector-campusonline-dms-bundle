<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\CreateDocumentController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\CreateDocumentVersionController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentProcessor;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentProvider;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\GetDocumentVersionContentController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[ApiResource(
    shortName: 'BlobConnectorCampusonlineDmsDocument',
    types: ['https://schema.org/Document'],
    operations: [
        new Get(
            uriTemplate: '/co-dms-api/api/documents/{uid}',
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: DocumentProvider::class
        ),
        new Get(
            uriTemplate: '/co-dms-api/api/documents/version/{uid}/content',
            outputFormats: [
                'octet_stream' => 'application/octet-stream',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: GetDocumentVersionContentController::class,
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                responses: [
                    200 => new Response(
                        content: new \ArrayObject([
                            'application/octet-stream' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                        ])
                    ),
                    415 => new Response(
                        content: new \ArrayObject([
                            'application/problem+json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ])
                    ),
                    'default' => new Response(
                        content: new \ArrayObject([
                            'application/problem+json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ])
                    ),
                ],
                summary: 'Retrieves the file content for a BlobConnectorCampusonlineDmsDocumentVersionInfo resource'
            ),
            read: false
        ),
        new Post(
            uriTemplate: '/co-dms-api/api/documents',
            inputFormats: ['multipart' => 'multipart/form-data'],
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: CreateDocumentController::class,
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'document_type' => [
                                        'type' => 'string',
                                        'example' => 'pdf',
                                    ],
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'filename.txt',
                                    ],
                                    'metadata' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                    'doc_version_metadata' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                    'binary_content' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            deserialize: false
        ),
        new Post(
            uriTemplate: '/co-dms-api/api/documents/{uid}/version',
            inputFormats: ['multipart' => 'multipart/form-data'],
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: CreateDocumentVersionController::class,
            openapi: new Operation(
                tags: ['Campusonline DMS'],
                summary: 'Creates a new version for a BlobConnectorCampusonlineDmsDocument resource',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'document_type' => [
                                        'type' => 'string',
                                        'example' => 'pdf',
                                    ],
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'filename.txt',
                                    ],
                                    'metadata' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                    'binary_content' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            deserialize: false
        ),
        new Delete(
            uriTemplate: '/co-dms-api/api/documents/{uid}',
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: DocumentProvider::class,
            processor: DocumentProcessor::class
        ),
    ],
    normalizationContext: [
        'groups' => ['BlobConnectorCampusonlineDmsDocument:output'],
        AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
    ],
    denormalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsDocument:input']]
)]
class Document
{
    #[ApiProperty(identifier: true)]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $uid = null;

    #[ApiProperty(
        openapiContext: [
            'type' => 'object',
            'example' => [
                'document_type' => 'transcript_of_records',
            ],
        ],
        jsonSchemaContext: [
            'type' => 'object',
        ]
    )]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    #[Context([Serializer::EMPTY_ARRAY_AS_OBJECT => true])]
    private ?array $metaData = null;

    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    private ?DocumentVersionInfo $latestVersion = null;

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getLatestVersion(): ?DocumentVersionInfo
    {
        return $this->latestVersion;
    }

    public function setLatestVersion(?DocumentVersionInfo $latestVersion): void
    {
        $this->latestVersion = $latestVersion;
    }
}
