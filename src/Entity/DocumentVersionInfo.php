<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DeleteDocumentVersionController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentVersionInfoProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[ApiResource(
    shortName: 'BlobConnectorCampusonlineDmsDocumentVersionInfo',
    types: ['https://schema.org/version'],
    operations: [
        new Get(
            uriTemplate: '/co-dms-api/api/documents/version/{uid}/metadata',
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: DocumentVersionInfoProvider::class
        ),
        new Delete(
            uriTemplate: '/co-dms-api/api/documents/version/{uid}',
            controller: DeleteDocumentVersionController::class,
            openapi: new Operation(
                tags: ['Campusonline DMS']
            ),
            provider: DocumentVersionInfoProvider::class
        ),
    ],
    normalizationContext: [
        'groups' => ['BlobConnectorCampusonlineDmsDocumentVersionInfo:output'],
        AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
    ],
)]
class DocumentVersionInfo
{
    #[ApiProperty(identifier: true)]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $uid = null;

    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $name = null;

    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $versionNumber = null;

    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $mediaType = null;

    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?int $size = null;

    #[ApiProperty(
        openapiContext: [
            'type' => 'object',
            'example' => [
                'document_version_type' => 'draft',
            ],
        ],
        jsonSchemaContext: [
            'type' => 'object',
        ]
    )]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    #[Context([Serializer::EMPTY_ARRAY_AS_OBJECT => true])]
    private ?array $metaData = null;

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

    public function getVersionNumber(): ?string
    {
        return $this->versionNumber;
    }

    public function setVersionNumber(?string $versionNumber): void
    {
        $this->versionNumber = $versionNumber;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(?string $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
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
