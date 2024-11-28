<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
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
            openapiContext: [
                'tags' => ['Campusonline DMS'],
            ],
            provider: DocumentProvider::class
        ),
        new Get(
            uriTemplate: '/co-dms-api/api/documents/version/{uid}/content',
            outputFormats: [
                'octet_stream' => 'application/octet-stream',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: GetDocumentVersionContentController::class,
            openapiContext: [
                'tags' => ['Campusonline DMS'],
                'summary' => 'Retrieves the file content for a BlobConnectorCampusonlineDmsDocumentVersionInfo resource',
                'responses' => [
                    '200' => [
                        'content' => [
                            'application/octet-stream' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                        ],
                    ],
                    '415' => [
                        'content' => [
                            'application/problem+json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                    'default' => [
                        'content' => [
                            'application/problem+json' => [
                                'schema' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
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
            openapiContext: [
                'tags' => ['Campusonline DMS'],
                'requestBody' => [
                    'content' => [
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
                    ],
                ],
            ],
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
            openapiContext: [
                'tags' => ['Campusonline DMS'],
                'summary' => 'Creates a new version for a BlobConnectorCampusonlineDmsDocument resource',
                'requestBody' => [
                    'content' => [
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
                    ],
                ],
            ],
            deserialize: false
        ),
        new Delete(
            uriTemplate: '/co-dms-api/api/documents/{uid}',
            openapiContext: [
                'tags' => ['Campusonline DMS'],
            ],
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

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    #[Context([Serializer::EMPTY_ARRAY_AS_OBJECT => true])]
    private ?array $metaData = null;

    #[ApiProperty(iris: ['https://schema.org/version'])]
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
