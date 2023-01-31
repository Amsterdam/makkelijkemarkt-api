<?php

namespace App\Normalizer;

use App\Entity\BtwType;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BtwTypeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof BtwType;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        /* @var BtwType $object */
        return [
            'id' => $object->getId(),
            'label' => $object->getLabel(),
        ];
    }
}
