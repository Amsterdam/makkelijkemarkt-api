<?php

namespace App\Utils;

class Helpers
{
    /**
     * Helper to parse geolocation.
     *
     * @param mixed $geoInput
     *
     * @return array<int, null> tupple
     */
    public static function parseGeolocation($geoInput): array
    {
        if ('' === $geoInput || null === $geoInput) {
            return [null, null];
        }

        if (false === is_array($geoInput)) {
            $geoInput = explode(',', $geoInput);
        }

        if (true === is_array($geoInput)) {
            if (0 === count($geoInput) || 1 === count($geoInput)) {
                return [null, null];
            }

            $geoInput = array_values($geoInput);
            $geoInput[0] = (float) $geoInput[0];
            $geoInput[1] = (float) $geoInput[1];

            return $geoInput;
        }
    }
}
