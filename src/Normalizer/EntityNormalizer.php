<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Vervanger;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

define('IMAGE_RESOLVE_PATH', 'media/cache/resolve/');

final class EntityNormalizer extends ObjectNormalizer
{
    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        parent::__construct($classMetadataFactory, $metadataAwareNameConverter, null, null);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return parent::supportsDenormalization($data, $type, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return parent::supportsNormalization($data, $format);
    }

    /**
     * @param array<mixed>|string|int|float|bool $data    The data from which to re-create the object
     * @param string|null                        $format  The format is optionally given to be able to denormalize
     *                                                    differently based on different input formats
     * @param array<mixed>                       $context Options for denormalizing
     *
     * @return object|object[]
     */
    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        return parent::denormalize($data, $class, $format, $context);
    }

    /**
     * @param mixed         $object  Object to normalize
     * @param array<string> $context Context options for the normalizer
     *
     * @return array<mixed>|string|int|float|bool|\ArrayObject|null \ArrayObject is used to make sure an empty object is encoded as an object not an array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var array<mixed> $data */
        $data = parent::normalize($object, $format, $context);

        $data = $this->handleDateTimeFormats($object, $data);
        $data = $this->handleFotoPathes($object, $data);
        $data = $this->handleShyProperties($object, $data);
        $data = $this->handleMarktIndelingslijst($object, $data);

        return $data;
    }

    /**
     * @param mixed        $object
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function handleDateTimeFormats($object, array $data): array
    {
        /** @var array<string> $properties */
        $properties = [
            'aangemaaktDatumtijd' => 'Y-m-d H:i:s',
            'aanmaakDatumtijd' => 'Y-m-d H:i:s',
            'afgevinktDatumtijd' => 'Y-m-d H:i:s',
            'creationDate' => 'Y-m-d H:i:s',
            'doorgehaaldDatumtijd' => 'Y-m-d H:i:s',
            'registratieDatumtijd' => 'Y-m-d H:i:s',
            'verwijderdDatumtijd' => 'Y-m-d H:i:s',
            'absentFrom' => 'Y-m-d',
            'absentUntil' => 'Y-m-d',
            'marktDate' => 'Y-m-d',
            'dag' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'handhavingsVerzoek' => 'Y-m-d',
            'geldigVanaf' => 'array',
            'geldigTot' => 'array',
            'patternDate' => 'Y-m-d H:i:s',
            'dateFrom' => 'Y-m-d',
        ];

        foreach ($properties as $prop => $dtFormat) {
            $method = 'get'.ucfirst($prop);

            if ('array' === $dtFormat && method_exists($object, $method)) {
                $data[$prop] = (array) $object->$method();
            } elseif (method_exists($object, $method) && null !== $object->$method()) {
                $data[$prop] = $object->$method()->format($dtFormat);
            }
        }

        return $data;
    }

    private function getBrowserPath(string $photo, string $imageSize): string
    {
        $base_url = '';
        if (isset($_SERVER['MM_API__BASE_URL'])) {
            $base_url = $_SERVER['MM_API__BASE_URL'];
        }

        return $base_url.IMAGE_RESOLVE_PATH.$imageSize.'/'.$photo;
    }

    /**
     * @param mixed        $object
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function handleFotoPathes($object, array $data): array
    {
        if (Koopman::class === get_class($object) || Vervanger::class === get_class($object)) {
            $fotoUrl = null;
            $fotoMediumUrl = null;

            if (null !== $object->getFoto()) {
                $fotoUrl = $this->getBrowserPath($object->getFoto(), 'koopman_rect_small');
                $fotoMediumUrl = $this->getBrowserPath($object->getFoto(), 'koopman_rect_medium');
            }

            $data['fotoUrl'] = $fotoUrl;
            $data['fotoMediumUrl'] = $fotoMediumUrl;
        }

        return $data;
    }

    /**
     * @param mixed        $object
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function handleShyProperties($object, array $data): array
    {
        if (Dagvergunning::class === get_class($object) && array_key_exists('controles', $data) && is_array($data['controles']) && count($data['controles']) < 1) {
            unset($data['controles']);
        }

        /*if (
            Koopman::class === get_class($object) &&
            array_key_exists('handhavingsVerzoek', $data) &&
            null === $data['handhavingsVerzoek']
        ) {
            unset($data['handhavingsVerzoek']);
        }*/

        return $data;
    }

    /**
     * @param mixed        $object
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function handleMarktIndelingslijst($object, array $data): array
    {
        if (Markt::class === get_class($object)) {
            $data['isABlijstIndeling'] = ('A/B-lijst' === $object->getIndelingstype());
        }

        return $data;
    }
}
