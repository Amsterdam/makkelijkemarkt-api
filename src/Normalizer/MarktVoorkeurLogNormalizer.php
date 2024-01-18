<?php

namespace App\Normalizer;

use App\Entity\MarktVoorkeur;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MarktVoorkeurLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof MarktVoorkeur;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        // Human readable string:
        // {actor} changed Rsvp for {koopmanName} on the {marktName} on {date} to {attending}

        /* @var MarktVoorkeur $object */
        return [
            'id' => $object->getId(),
            'koopman' => $object->getKoopman(),
            'anywhere' => $object->getAnywhere(),
            'minimum' => $object->getMinimum(),
            'maximum' => $object->getMaximum(),
            'inrichting' => $object->getHasInrichting(),
            'bakType' => $object->getBakType(),
            'branche' => $object->getBranche(),
            'absentFrom' => $object->getAbsentFrom() ? $object->getAbsentFrom()->format('Y-m-d') : '',
            'absentUntil' => $object->getAbsentUntil() ? $object->getAbsentUntil()->format('Y-m-d') : '',
            'markt' => $object->getMarkt(),
        ];
    }
}
