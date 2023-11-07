<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

// Sends a 200 response but with a warning object.
// Frontend should know what to do based on the type of warning.
class WarningResponse extends JsonResponse
{
    // TODO make this enum when php 8.1
    const DUBBELSTAAN = 'DUBBELSTAAN';

    const MESSAGES = [
        self::DUBBELSTAAN => 'Ondernemer is registered on too many markets.',
    ];

    public function __construct($content = [], $status = 200, $headers = [])
    {
        if (!isset($content['type'])
            || !defined(self::class.'::'.$content['type'])
        ) {
            throw new \Exception('WarningResponse requires a warning and a known type definition');
        }

        $body = [
            'warning' => [
                'type' => $content['type'],
                'message' => self::MESSAGES[$content['type']] ?? '',
                'data' => $content['data'] ?? [],
            ],
        ];

        parent::__construct($body, $status, $headers);
    }
}
