<?php

namespace App\Normalizer;

use App\Entity\Tarievenplan;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TarievenplanNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Tarievenplan;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $dateUntil = $object->getDateUntil();

        /* @var Tarievenplan $object */
        return [
            'id' => $object->getId(),
            'marktId' => $object->getMarkt()->getId(),
            'marktName' => $object->getMarkt()->getNaam(),
            'name' => $object->getName(),
            'type' => $object->getType(),
            'dateFrom' => $object->getDateFrom()->format('Y-m-d'),
            'dateUntil' => $dateUntil ? $dateUntil->format('Y-m-d') : '',
            'tarieven' => $this->normalizer->normalize($object->getActiveTarieven(), $format, $context),
            'weekdays' => $object->getAllWeekdays(),
            'variant' => $object->getVariant(),
            'ignoreVastePlaats' => $object->isIgnoreVastePlaats(),
        ];
    }
}
