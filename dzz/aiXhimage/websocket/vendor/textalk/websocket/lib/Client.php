<?php

/**
 * Copyright (C) 2014-2022 Textalk/Abicart and contributors.
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket;

use ErrorException;
use InvalidArgumentException;
use Phrity\Net\Uri;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\UriInterface;
use Psr\Log\{
    LoggerAwareInterface,
    LoggerAwareTrait,
    LoggerInterface,
    NullLogger
};
use WebSocket\Message\Factory;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait; // provides setLogger(LoggerInterface $logger)
    use OpcodeTrait;

    // Default options
    protected static $default_options = [
        'context'       => null,
        'filter'        => ['text', 'binary'],
        'fragment_size' => 4096,
        'headers'       => null,
        'logger'        => null,
        'origin'        => null, // @deprecated
        'persistent'    => false,
        'return_obj'    => false,
        'timeout'       => 5,
    ];

    private $socket_uri;
    private $connection;
    private $options = [];
    private $listen = false;
    private $last_opcode = null;


    /* ---------- Magic methods ------------------------------------------------------ */

    /**
     * @param UriInterface|string $uri     A ws/wss-URI
     * @param array               $options
     *   Associative array containing:
     *   - context:       Set the stream context. Default: empty context
     *   - timeout:       Set the socket timeout in seconds.  Default: 5
     *   - fragment_size: Set framgemnt size.  Default: 4096
     *   - headers:       Associative array of headers to set/override.
     */
    public function __construct($uri, array $options = [])
    {
        $this->socket_uri = $this->parseUri($uri);
        $this->options = array_merge(self::$default_options, [
            'logger' => new NullLogger(),
        ], $options);
        $this->setLogger($this->options['logger']);
    }

    /**
     * Get string representation of instance.
     * @return string String representation.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s(%s)",
            get_class($this),
            $this->getName() ?: 'closed'
        );
    }


    /* ---------- Client option functions -------------------------------------------- */

    /**
     * Set timeout.
     * @param int $timeout Timeout in seconds.
     */
    public function setTimeout(int $timeout): void
    {
        $this->options['timeout'] = $timeout;
        if (!$this->isConnected()) {
            return;
        }
        $this->connection->setTimeout($timeout);
        $this->connection->setOptions($this->options);
    }

    /**
     * Set fragmentation size.
     * @param int $fragment_size Fragment size in bytes.
     * @return self.
     */
    public function setFragmentSize(int $fragment_size): self
    {
        $this->options['fragment_size'] = $fragment_size;
        $this->connection->setOptions($this->options);
        return $this;
    }

    /**
     * Get fragmentation size.
     * @return int $fragment_size Fragment size in bytes.
     */
    public function getFragmentSize(): int
    {
        return $this->options['fragment_size'];
    }


    /* ---------- Connection operations ---------------------------------------------- */

    /**
     * Send text message.
     * @param string $payload Content as string.
     */
    public function text(string $payload): void
    {
        $this->send($payload);
    }

    /**
     * Send binary message.
     * @param string $payload Content as binary string.
     */
    public function binary(string $payload): void
    {
        $this->send($payload, 'binary');
    }

    /**
     * Send ping.
     * @param string $payload Optional text as string.
     */
    public function ping(string $payload = ''): void
    {
        $this->send($payload, 'ping');
    }

    /**
     * Send unsolicited pong.
     * @param string $payload Optional text as string.
     */
    public function pong(string $payload = ''): void
    {
        $this->send($payload, 'pong');
    }

    /**
     * Send message.
     * @param string $payload Message to send.
     * @param string $opcode Opcode to use, default: 'text'.
     * @param bool $masked If message should be masked default: true.
     */
    public function send(string $payload, string $opcode = 'text', bool $masked = true): void
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        if (!in_array($opcode, array_keys(self::$opcodes))) {
            $warning = "Bad opcode '{$opcode}'.  Try 'text' or 'binary'.";
            $this->logger->warning($warning);
            throw new BadOpcodeException($warning);
        }

        $factory = new Factory();
        $message = $factory->create($opcode, $payload);
        $this->connection->pushMessage($message, $masked);
    }

    /**
     * Tell the socket to close.
     * @param integer $status  http://tools.ietf.org/html/rfc6455#section-7.4
     * @param string  $message A closing message, max 125 bytes.
     */
    public function close(int $status = 1000, string $message = 'ttfn'): void
    {
        if (!$this->isConnected()) {
            return;
        }
        $this->connection->close($status, $message);
    }

    /**
     * Disconnect from server.
     */
    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->connection->disconnect();
        }
    }

    /**
     * Receive message.
     * Note that this operation will block reading.
     * @return mixed Message, text or null depending on settings.
     */
    public function receive()
    {
        $filter = $this->options['filter'];
        $return_obj = $this->options['return_obj'];

        if (!$this->isConnected()) {
            $this->connect();
        }

        while (true) {
            $message = $this->connection->pullMessage();
            $opcode = $message->getOpcode();
            if (in_array($opcode, $filter)) {
                $this->last_opcode = $opcode;
                $return = $return_obj ? $message : $message->getContent();
                break;
            } elseif ($opcode == 'close') {
                $this->last_opcode = null;
                $return = $return_obj ? $message : null;
                break;
            }
        }
        return $return;
    }


    /* ---------- Connection functions ----------------------------------------------- */

    /**
     * Get last received opcode.
     * @return string|null Opcode.
     */
    public function getLastOpcode(): ?string
    {
        return $this->last_opcode;
    }

    /**
     * Get close status on connection.
     * @return int|null Close status.
     */
    public function getCloseStatus(): ?int
    {
        return $this->connection ? $this->connection->getCloseStatus() : null;
    }

    /**
     * If Client has active connection.
     * @return bool True if active connection.
     */
    public function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected();
    }

    /**
     * Get name of local socket, or null if not connected.
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->isConnected() ? $this->connection->getName() : null;
    }

    /**
     * Get name of remote socket, or null if not connected.
     * @return string|null
     */
    public function getRemoteName(): ?string
    {
        return $this->isConnected() ? $this->connection->getRemoteName() : null;
    }

    /**
     * Get name of remote socket, or null if not connected.
     * @return string|null
     * @deprecated Will be removed in future version, use getPeer() instead.
     */
    public function getPier(): ?string
    {
        trigger_error(
            'getPier() is deprecated and will be removed in future version. Use getRemoteName() instead.',
            E_USER_DEPRECATED
        );
        return $this->getRemoteName();
    }


    /* ---------- Helper functions --------------------------------------------------- */

    /**
     * Perform WebSocket handshake
     */
    protected function connect(): void
    {
        $this->connection = null;

        $host_uri = $this->socket_uri
            ->withScheme($this->socket_uri->getScheme() == 'wss' ? 'ssl' : 'tcp')
            ->withPort($this->socket_uri->getPort() ?? ($this->socket_uri->getScheme() == 'wss' ? 443 : 80))
            ->withPath('')
            ->withQuery('')
            ->withFragment('')
            ->withUserInfo('');

        // Path must be absolute
        $http_path = $this->socket_uri->getPath();
        if ($http_path === '' || $http_path[0] !== '/') {
            $http_path = "/{$http_path}";
        }

        $http_uri = (new Uri())
            ->withPath($http_path)
            ->withQuery($this->socket_uri->getQuery());

        // Set the stream context options if they're already set in the config
        if (isset($this->options['context'])) {
            // Suppress the error since we'll catch it below
            if (@get_resource_type($this->options['context']) === 'stream-context') {
                $context = $this->options['context'];
            } else {
                $error = "Stream context in \$options['context'] isn't a valid context.";
                $this->logger->error($error);
                throw new \InvalidArgumentException($error);
            }
        } else {
            $context = stream_context_create();
        }

        $persistent = $this->options['persistent'] === true;
        $flags = STREAM_CLIENT_CONNECT;
        $flags = $persistent ? $flags | STREAM_CLIENT_PERSISTENT : $flags;
        $socket = null;

        try {
            $handler = new ErrorHandler();
            $socket = $handler->with(function () use ($host_uri, $flags, $context) {
                $error = $errno = $errstr = null;
                // Open the socket.
                return stream_socket_client(
                    $host_uri,
                    $errno,
                    $errstr,
                    $this->options['timeout'],
                    $flags,
                    $context
                );
            });
            if (!$socket) {
                throw new ErrorException('No socket');
            }
        } catch (ErrorException $e) {
            $error = "Could not open socket to \"{$host_uri->getAuthority()}\": {$e->getMessage()} ({$e->getCode()}).";
            $this->logger->error($error, ['severity' => $e->getSeverity()]);
            throw new ConnectionException($error, 0, [], $e);
        }

        $this->connection = new Connection($socket, $this->options);
        $this->connection->setLogger($this->logger);
        if (!$this->isConnected()) {
            $error = "Invalid stream on \"{$host_uri->getAuthority()}\".";
            $this->logger->error($error);
            throw new ConnectionException($error);
        }

        if (!$persistent || $this->connection->tell() == 0) {
            // Set timeout on the stream as well.
            $this->connection->setTimeout($this->options['timeout']);

            // Generate the WebSocket key.
            $key = self::generateKey();

            // Default headers
            $headers = [
                'Host'                  => $host_uri->getAuthority(),
                'User-Agent'            => 'websocket-client-php',
                'Connection'            => 'Upgrade',
                'Upgrade'               => 'websocket',
                'Sec-WebSocket-Key'     => $key,
                'Sec-WebSocket-Version' => '13',
            ];

            // Handle basic authentication.
            if ($userinfo = $this->socket_uri->getUserInfo()) {
                $headers['authorization'] = 'Basic ' . base64_encode($userinfo);
            }

            // Deprecated way of adding origin (use headers instead).
            if (isset($this->options['origin'])) {
                $headers['origin'] = $this->options['origin'];
            }

            // Add and override with headers from options.
            if (isset($this->options['headers'])) {
                $headers = array_merge($headers, $this->options['headers']);
            }

            $header = "GET {$http_uri} HTTP/1.1\r\n" . implode(
                "\r\n",
                array_map(
                    function ($key, $value) {
                        return "$key: $value";
                    },
                    array_keys($headers),
                    $headers
                )
            ) . "\r\n\r\n";

            // Send headers.
            $this->connection->write($header);

            // Get server response header (terminated with double CR+LF).
            $response = '';
            try {
                do {
                    $buffer = $this->connection->gets(1024);
                    $response .= $buffer;
                } while (substr_count($response, "\r\n\r\n") == 0);
            } catch (Exception $e) {
                throw new ConnectionException('Client handshake error', $e->getCode(), $e->getData(), $e);
            }

            // Validate response.
            if (!preg_match('#Sec-WebSocket-Accept:\s(.*)$#mUi', $response, $matches)) {
                $error = sprintf(
                    "Connection to '%s' failed: Server sent invalid upgrade response: %s",
                    (string)$this->socket_uri,
                    (string)$response
                );
                $this->logger->error($error);
                throw new ConnectionException($error);
            }

            $keyAccept = trim($matches[1]);
            $expectedResonse = base64_encode(
                pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))
            );

            if ($keyAccept !== $expectedResonse) {
                $error = 'Server sent bad upgrade response.';
                $this->logger->error($error);
                throw new ConnectionException($error);
            }
        }

        $this->logger->info("Client connected to {$this->socket_uri}");
    }

    /**
     * Generate a random string for WebSocket key.
     * @return string Random string
     */
    protected static function generateKey(): string
    {
        $key = '';
        for ($i = 0; $i < 16; $i++) {
            $key .= chr(rand(33, 126));
        }
        return base64_encode($key);
    }

    protected function parseUri($uri): UriInterface
    {
        if ($uri instanceof UriInterface) {
            $uri = $uri;
        } elseif (is_string($uri)) {
            try {
                $uri = new Uri($uri);
            } catch (InvalidArgumentException $e) {
                throw new BadUriException("Invalid URI '{$uri}' provided.", 0, $e);
            }
        } else {
            throw new BadUriException("Provided URI must be a UriInterface or string.");
        }
        if (!in_array($uri->getScheme(), ['ws', 'wss'])) {
            throw new BadUriException("Invalid URI scheme, must be 'ws' or 'wss'.");
        }
        return $uri;
    }
}
