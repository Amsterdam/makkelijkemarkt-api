<?php

namespace App\Normalizer;

use App\Entity\BtwPlan;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BtwPlanLogNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof BtwPlan;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        /* @var BtwPlan $object */
        return [
            'id' => $object->getId(),
            'tarief_soort_id' => $object->getTariefSoort(),
            'btw_type_id' => $object->getBtwType(),
            'date_from' => $object->getDateFrom()->format('Y-m-d'),
            'markt_id' => $object->getMarkt(),
        ];
    }
}
