<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class LogFormatter extends LineFormatter
{
    public function __construct(
        $format = null,
        $dateFormat = null,
        $allowInlineLineBreaks = false,
        $ignoreEmptyContextAndExtra = false
    ) {
        // This will prevent empty []'s in your logs when the extra field is empty in the Logformatter.
        $ignoreEmptyContextAndExtra = true;
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }
}
