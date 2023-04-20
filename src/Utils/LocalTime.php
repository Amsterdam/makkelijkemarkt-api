<?php

namespace App\Utils;

use DateTime;
use DateTimeZone;

class LocalTime extends DateTime
{
    private DateTime $dateTime;

    public function __construct()
    {
        $this->dateTime = (new DateTime())->setTimezone(new DateTimeZone('Europe/Amsterdam'));
    }
}
