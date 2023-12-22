<?php

namespace App\Normalizer;

use App\Entity\Tarief;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TariefNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Tarief;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /* @var Tarief $object */
        return [
            'id' => $object->getId(),
            'tarievenplanId' => $object->getTarievenplan()->getId(),
            'tariefSoortId' => $object->getTariefSoort()->getId(),
            'tarief' => $object->getTarief(),
        ];
    }
}
