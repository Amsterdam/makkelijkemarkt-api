<?php

namespace App\Utils;

class Filters
{
    // Given an array of entities, return the entity with the given id
    public static function getEntityInListById(int $entityId, array $entityList)
    {
        foreach ($entityList as $entity) {
            if ($entity->getId() === $entityId) {
                return $entity;
            }
        }

        return null;
    }

    // Given an an array with properties, filter out properties with the given vaulues
    // Example: $array = ['elektra': 0, '4meterKraam': 1, 'krachtstroom': false],
    // $values = [0, false], returns ['4meterKraam' => 1]
    public static function filterOutValuesFromArray(array $array, array $values): array
    {
        return array_filter($array, function ($value) use ($values) {
            return !in_array($value, $values);
        });
    }
}
