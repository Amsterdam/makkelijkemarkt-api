<?php

namespace App\Normalizer;

use App\Entity\DagvergunningMapping;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DagvergunningMappingNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct()
    {
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof DagvergunningMapping;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $archivedOn = $object->getArchivedOn();
        $tariefSoort = $object->getTariefsoort();

        /* @var DagvergunningMapping $object */
        return [
            'id' => $object->getId(),
            'dagvergunningKey' => $object->getDagvergunningKey(),
            'mercatoKey' => $object->getMercatoKey(),
            'translatedToUnit' => $object->getTranslatedToUnit(),
            'tariefType' => $object->getTariefType(),
            'unit' => $object->getUnit(),
            'archivedOn' => $archivedOn ? $archivedOn->format('Y-m-d') : null,
            'tariefSoortLabel' => $tariefSoort ? $tariefSoort->getLabel() : null,
            'tariefSoortId' => $tariefSoort ? $tariefSoort->getId() : null,
            'appLabel' => $object->getAppLabel(),
            'inputType' => $object->getInputType(),
        ];
    }
}
