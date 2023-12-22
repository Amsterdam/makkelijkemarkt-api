<?php

namespace App\Normalizer;

use App\Entity\TariefSoort;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TariefSoortNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof TariefSoort;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /* @var BtwType $object */
        return [
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'tariefType' => $object->getTariefType(),
            'deleted' => $object->getDeleted(),
            'unit' => $object->getUnit(),
            'factuurLabel' => $object->getFactuurLabel(),
        ];
    }
}
