<?php

namespace App\Normalizer;

use App\Entity\BtwWaarde;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BtwWaardeLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof BtwWaarde;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /* @var BtwWaarde $object */
        return [
            'id' => $object->getId(),
            'btw_type' => $object->getBtwType(),
            'date_from' => $object->getDateFrom()->format('Y-m-d'),
            'tarief' => $object->getTarief(),
        ];
    }
}
