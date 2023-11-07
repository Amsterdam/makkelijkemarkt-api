<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

// Sends a 200 response but with a warning object.
// Frontend should know what to do based on the type of warning.
// TODO should there also be a warning message included?
class WarningResponse extends JsonResponse
{
    const TYPES = [
        'DUBBELSTAAN' => 'DUBBELSTAAN',
    ];

    public function __construct($content = [], $status = 200, $headers = [])
    {
        if (!isset($content['type'])
            || !in_array($content['type'], array_values(self::TYPES))
        ) {
            throw new \Exception('WarningResponse requires a warning and a known type definition');
        }

        $body = [
            'warning' => [
                'type' => $content['type'],
                'message' => $content['message'] ?? '',
                'data' => $content['data'] ?? [],
            ],
        ];

        parent::__construct($body, $status, $headers);
    }
}
