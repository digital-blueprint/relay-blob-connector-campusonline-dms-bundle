<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, $format = null, array $context = []): array
    {
        $normalized = $this->normalizer->normalize($object, $format, $context);
        $normalized['foo'] = 'bar';
        dump($normalized);

        return $normalized;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            ($format === 'jsonld' || $format === 'jsonproblem')
            && ($data instanceof FlattenException);
    }

    public function getSupportedTypes(?string $format): array
    {
        if ($format === 'jsonld' || $format === 'jsonproblem') {
            return [
                FlattenException::class => false,
            ];
        }

        return [];
    }
}
