<?php

namespace App\Normalizer;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TokenNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Token;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /* @var Token $object */
        return [
            'uuid' => $object->getUuid(),
            'creationDate' => $object->getCreationDate()->format('Y-m-d H:i:s'),
            'lifeTime' => $object->getLifeTime(),
            'timeLeft' => $object->getTimeLeft(),
            'deviceUuid' => $object->getDeviceUuid(),
            'clientApp' => $object->getClientApp(),
            'clientVersion' => $object->getClientVersion(),
            'account' => $this->normalizer->normalize($object->getAccount(), $format, $context),
            'featureFlags' => $this->normalizer->normalize($object->getFeatureFlags(), $format, $context),
        ];
    }
}
