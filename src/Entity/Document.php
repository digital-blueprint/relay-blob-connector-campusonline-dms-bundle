<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\CreateDocumentController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\CreateDocumentVersionController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentProvider;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\GetDocumentVersionContentController;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'BlobConnectorCampusonlineDmsDocument',
    types: ['https://schema.org/Document'],
    operations: [
        new Get(
            uriTemplate: '/co-dp-dms-adapter-d3/api/documents/{uid}',
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
            uriTemplate: '/co-dp-dms-adapter-d3/api/version/{uid}',
            outputFormats: [
                'octet_stream' => 'application/octet-stream',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: GetDocumentVersionContentController::class,
            openapiContext: [
                'tags' => ['Campusonline DMS'],
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
            uriTemplate: '/co-dp-dms-adapter-d3/api/documents',
            inputFormats: ['form_data' => 'multipart/form-data'],
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
                                    'documentType' => [
                                        'type' => 'string',
                                        'example' => 'pdf',
                                    ],
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'filename.txt',
                                    ],
                                    'metaData' => [
                                        'type' => 'object',
                                        'example' => '{"foo": "bar"}',
                                    ],
                                    'content' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                                'required' => ['name'],
                            ],
                        ],
                    ],
                ],
            ],
            deserialize: false
        ),
        new Post(
            uriTemplate: '/co-dp-dms-adapter-d3/api/documents/{uid}/version',
            inputFormats: ['octet_stream' => 'application/octet-stream'],
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            controller: CreateDocumentVersionController::class,
            openapiContext: [
                'tags' => ['Campusonline DMS'],
                'requestBody' => [
                    'content' => [
                        'application/octet-stream' => [
                            'schema' => [
                                'type' => 'string',
                                'format' => 'binary',
                            ],
                        ],
                    ],
                ],
            ],
            deserialize: false
        ),
    ],
    normalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsDocument:output']],
    denormalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsDocument:input']],
    extraProperties: ['rfc_7807_compliant_errors' => true]
)]
class Document
{
    #[ApiProperty(identifier: true)]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $uid = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output', 'BlobConnectorCampusonlineDmsDocument:input'])]
    private ?string $documentType = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output', 'BlobConnectorCampusonlineDmsDocument:input'])]
    private ?string $name = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output', 'BlobConnectorCampusonlineDmsDocument:input'])]
    private ?array $metaData = null;

    #[ApiProperty(iris: ['https://schema.org/version'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocument:output'])]
    private ?DocumentVersionInfo $latestContent = null;

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDocumentType(): ?string
    {
        return $this->documentType;
    }

    public function setDocumentType(?string $documentType): void
    {
        $this->documentType = $documentType;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function getLatestContent(): ?DocumentVersionInfo
    {
        return $this->latestContent;
    }

    public function setLatestContent(?DocumentVersionInfo $latestContent): void
    {
        $this->latestContent = $latestContent;
    }
}
