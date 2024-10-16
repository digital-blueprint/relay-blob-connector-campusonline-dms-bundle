<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DeleteDocumentVersionController;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentVersionInfoProvider;
use Symfony\Component\Serializer\Annotation\Groups;

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
            openapiContext: [
                'tags' => ['Campusonline DMS'],
            ],
            provider: DocumentVersionInfoProvider::class
        ),
        new Delete(
            uriTemplate: '/co-dms-api/api/documents/version/{uid}',
            controller: DeleteDocumentVersionController::class,
            openapiContext: [
                'tags' => ['Campusonline DMS'],
            ],
            provider: DocumentVersionInfoProvider::class
        ),
    ],
    normalizationContext: ['groups' => ['BlobConnectorCampusonlineDmsDocumentVersionInfo:output']],
)]
class DocumentVersionInfo
{
    #[ApiProperty(identifier: true)]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $uid = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $name = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $versionNumber = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $mediaType = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?int $size = null;

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
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
