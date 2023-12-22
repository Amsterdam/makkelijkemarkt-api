<?php

namespace App\Normalizer;

use App\Entity\BtwPlan;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BtwPlanNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof BtwPlan;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $markt = $object->getMarkt();

        /* @var BtwPlan $object */
        return [
            'id' => $object->getId(),
            'tariefSoortId' => $object->getTariefSoort()->getId(),
            'tariefLabel' => $object->getTariefSoort()->getLabel(),
            'tariefType' => $object->getTariefSoort()->getTariefType(),
            'btwTypeId' => $object->getBtwType()->getId(),
            'btwType' => $object->getBtwType()->getLabel(),
            'dateFrom' => $object->getDateFrom()->format('Y-m-d'),
            'marktId' => $markt ? $markt->getId() : null,
            'marktName' => $markt ? $markt->getNaam() : null,
        ];
    }
}
