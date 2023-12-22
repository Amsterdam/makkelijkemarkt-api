<?php

namespace App\Utils;

class LocalTime extends \DateTime
{
    public function __construct()
    {
        $timezone = new \DateTimeZone('Europe/Amsterdam');
        parent::__construct('now', $timezone);
    }
}
