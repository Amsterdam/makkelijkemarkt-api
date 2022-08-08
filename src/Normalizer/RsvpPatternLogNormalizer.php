<?php

namespace App\Normalizer;

use App\Entity\RsvpPattern;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RsvpPatternLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof RsvpPattern;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        /* @var RsvpPattern $object */
        return [
            'id' => $object->getId(),
            'koopman' => $object->getKoopman(),
            'markt' => $object->getMarkt(),
            'monday' => $object->getMonday(),
            'tuesday' => $object->getTuesday(),
            'wednesday' => $object->getWednesday(),
            'thursday' => $object->getThursday(),
            'friday' => $object->getFriday(),
            'saturday' => $object->getSaturday(),
            'sunday' => $object->getSunday(),
        ];
    }
}
