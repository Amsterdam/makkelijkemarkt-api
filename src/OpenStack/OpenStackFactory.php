<?php

declare(strict_types=1);

namespace App\OpenStack;

use OpenStack\OpenStack;

/**
 * TODO change to V3.
 *
 * @see https://php-openstack-sdk.readthedocs.io/en/latest/services/identity/v2/authentication.html#identity-v2-0
 */
final class OpenStackFactory
{
    private $authOptions;

    public function __construct(
        array $authOptions
    ) {
        $this->authOptions = $authOptions;

        // $requestUri = $_SERVER['REQUEST_URI'];
        $bla = 'bloe';
    }

    public function __invoke(): OpenStack
    {
        try {
            $openStack = new OpenStack($this->authOptions);

            return $openStack;
        } catch (\Error $error) {
            throw $error;
        }
    }
}
