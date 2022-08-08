<?php

namespace App\Normalizer;

use App\Entity\Rsvp;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RsvpLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof Rsvp;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        /* @var Rsvp $object */
        return [
            'id' => $object->getId(),
            'koopman' => $object->getKoopman(),
            'markt' => $object->getMarkt(),
            'marktDate' => $object->getMarktDate()->format('Y-m-d'),
            'attending' => $object->getAttending(),
        ];
    }
}
