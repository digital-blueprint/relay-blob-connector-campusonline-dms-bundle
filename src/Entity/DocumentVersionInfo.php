<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\DocumentVersionInfoProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'BlobConnectorCampusonlineDmsDocumentVersionInfo',
    types: ['https://schema.org/version'],
    operations: [
        new Get(
            uriTemplate: '/co-dp-dms-adapter-d3/api/documents/version/{uid}/info',
            outputFormats: [
                'json' => 'application/json',
                'jsonproblem' => 'application/problem+json',
            ],
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

    #[ApiProperty(iris: ['https://schema.org/additionalProperty'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $version = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?string $mediaType = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['BlobConnectorCampusonlineDmsDocumentVersionInfo:output', 'BlobConnectorCampusonlineDmsDocument:output'])]
    private ?int $size = null;

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
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
}
