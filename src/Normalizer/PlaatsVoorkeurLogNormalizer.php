<?php

namespace App\Normalizer;

use App\Entity\PlaatsVoorkeur;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlaatsVoorkeurLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof PlaatsVoorkeur;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        // Human readable string:
        // {actor} changed PlaatsVoorkeur for {koopmanName} on the {marktName} to {plaatsen}

        /* @var PlaatsVoorkeur $object */
        return [
            'id' => $object->getId(),
            'plaatsen' => $object->getPlaatsen(),
            'markt' => $object->getMarkt(),
            'koopman' => $object->getKoopman(),
        ];
    }
}
