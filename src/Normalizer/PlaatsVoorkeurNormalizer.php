<?php

namespace App\Normalizer;

use App\Entity\PlaatsVoorkeur;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlaatsVoorkeurNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
        /* @var PlaatsVoorkeur $object */
        return [
            'id' => $object->getId(),
            'plaatsen' => $object->getPlaatsen(),
            'markt' => $object->getMarkt(),
            'koopman' => $object->getKoopman(),
        ];
    }
}
