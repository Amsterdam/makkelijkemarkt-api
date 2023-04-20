<?php

namespace App\Utils;

use DateTime;
use DateTimeZone;

class LocalTime extends DateTime
{
    public function __construct()
    {
        $timezone = new DateTimeZone('Europe/Amsterdam');
        parent::__construct('now', $timezone);
    }
}
