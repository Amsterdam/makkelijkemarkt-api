<?php

namespace App\Normalizer;

use App\Entity\Allocation;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AllocationLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Allocation;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /* @var Allocation $object */
        return [
            'id' => $object->getId(),
            'isAllocated' => $object->getIsAllocated(),
            'plaatsen' => $object->getPlaatsen(),
            'plaatsvoorkeuren' => $object->getPlaatsvoorkeuren(),
            'rejectReason' => $object->getrejectReason(),
            'date' => $object->getDate(),
            'anywhere' => $object->getAnywhere(),
            'minimum' => $object->getMinimum(),
            'maximum' => $object->getMaximum(),
            'baktype' => $object->getBakType(),
            'hasInrichting' => $object->getHasInrichting(),
            'markt' => $object->getMarkt(),
            'koopman' => $object->getKoopman(),
            'branche' => $object->getBranche(),
        ];
    }
}
