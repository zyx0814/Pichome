<?php

/**
 * File for Net\UriFactory class.
 * @package Phrity > Net > Uri
 * @see https://www.rfc-editor.org/rfc/rfc3986
 * @see https://www.php-fig.org/psr/psr-17/#26-urifactoryinterface
 */

namespace Phrity\Net;

use Psr\Http\Message\{
    UriFactoryInterface,
    UriInterface
};

/**
 * Net\UriFactory class.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * Create a new URI.
     * @param string $uri The URI to parse.
     * @throws \InvalidArgumentException If the given URI cannot be parsed
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
