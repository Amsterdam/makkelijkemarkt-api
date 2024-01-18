<?php

namespace App\Utils;

class Constants
{
    public static function getAllocationTime(): \DateTime
    {
        return (new \DateTime('today'))
            ->setTimezone(new \DateTimeZone('Europe/Amsterdam'))
            ->setTime(15, 0);
    }

    public static function getWeekdays(): array
    {
        return [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }
}
