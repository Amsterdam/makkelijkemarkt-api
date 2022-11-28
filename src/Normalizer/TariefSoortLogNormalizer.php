<?php

namespace App\Normalizer;

use App\Entity\TariefSoort;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TariefSoortLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof TariefSoort;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        /* @var TariefSoort $object */
        return [
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'tarief_type' => $object->getTariefType(),
            'deleted' => $object->getDeleted(),
        ];
    }
}
