Client • [Server](Server.md) • [Message](Message.md) • [Examples](Examples.md) • [Changelog](Changelog.md) • [Contributing](Contributing.md)

# Websocket: Client

The client can read and write on a WebSocket stream.
It internally supports Upgrade handshake and implicit close and ping/pong operations.

##  Class synopsis

```php
WebSocket\Client {

    public __construct(UriInterface|string $uri, array $options = []);
    public __destruct();
    public __toString() : string;

    public text(string $payload) : void;
    public binary(string $payload) : void;
    public ping(string $payload = '') : void;
    public pong(string $payload = '') : void;
    public send(Message|string $payload, string $opcode = 'text', bool $masked = true) : void;
    public close(int $status = 1000, mixed $message = 'ttfn') : void;
    public receive() : Message|string|null;

    public getName() : string|null;
    public getRemoteName() : string|null;
    public getLastOpcode() : string;
    public getCloseStatus() : int;
    public isConnected() : bool;
    public setTimeout(int $seconds) : void;
    public setFragmentSize(int $fragment_size) : self;
    public getFragmentSize() : int;
    public setLogger(Psr\Log\LoggerInterface $logger = null) : void;
}
```

## Examples

### Simple send-receive operation

This example send a single message to a server, and output the response.

```php
$client = new WebSocket\Client("ws://echo.websocket.org/");
$client->text("Hello WebSocket.org!");
echo $client->receive();
$client->close();
```

### Listening to a server

To continuously listen to incoming messages, you need to put the receive operation within a loop.
Note that these functions **always** throw exception on any failure, including recoverable failures such as connection time out.
By consuming exceptions, the code will re-connect the socket in next loop iteration.

```php
$client = new WebSocket\Client("ws://echo.websocket.org/");
while (true) {
    try {
        $message = $client->receive();
        // Act on received message
        // Break while loop to stop listening
    } catch (\WebSocket\ConnectionException $e) {
        // Possibly log errors
    }
}
$client->close();
```

### Filtering received messages

By default the `receive()` method return messages of 'text' and 'binary' opcode.
The filter option allows you to specify which message types to return.

```php
$client = new WebSocket\Client("ws://echo.websocket.org/", ['filter' => ['text']]);
$client->receive(); // Only return 'text' messages

$client = new WebSocket\Client("ws://echo.websocket.org/", ['filter' => ['text', 'binary', 'ping', 'pong', 'close']]);
$client->receive(); // Return all messages
```

### Sending messages

There are convenience methods to send messages with different opcodes.
```php
$client = new WebSocket\Client("ws://echo.websocket.org/");

// Convenience methods
$client->text('A plain text message'); // Send an opcode=text message
$client->binary($binary_string); // Send an opcode=binary message
$client->ping(); // Send an opcode=ping frame
$client->pong(); // Send an unsolicited opcode=pong frame

// Generic send method
$client->send($payload); // Sent as masked opcode=text
$client->send($payload, 'binary'); // Sent as masked opcode=binary
$client->send($payload, 'binary', false); // Sent as unmasked opcode=binary
```

## Constructor options

The `$options` parameter in constructor accepts an associative array of options.

* `context` - A stream context created using [stream_context_create](https://www.php.net/manual/en/function.stream-context-create).
* `filter` - Array of opcodes to return on receive, default `['text', 'binary']`
* `fragment_size` - Maximum payload size. Default 4096 chars.
* `headers` - Additional headers as associative array name => content.
* `logger` - A [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger.
* `persistent` - Connection is re-used between requests until time out is reached. Default false.
* `return_obj` - Return a [Message](Message.md) instance on receive, default false
* `timeout` - Time out in seconds. Default 5 seconds.

```php
$context = stream_context_create();
stream_context_set_option($context, 'ssl', 'verify_peer', false);
stream_context_set_option($context, 'ssl', 'verify_peer_name', false);

$client = new WebSocket\Client("ws://echo.websocket.org/", [
    'context' => $context, // Attach stream context created above
    'filter' => ['text', 'binary', 'ping'], // Specify message types for receive() to return
    'headers' => [ // Additional headers, used to specify subprotocol
        'Sec-WebSocket-Protocol' => 'soap',
        'origin' => 'localhost',
    ],
    'logger' => $my_psr3_logger, // Attach a PSR3 compatible logger
    'return_obj' => true, // Return Message instance rather than just text
    'timeout' => 60, // 1 minute time out
]);
```

## Exceptions

* `WebSocket\BadOpcodeException` - Thrown if provided opcode is invalid.
* `WebSocket\BadUriException` - Thrown if provided URI is invalid.
* `WebSocket\ConnectionException` - Thrown on any socket I/O failure.
* `WebSocket\TimeoutException` - Thrown when the socket experiences a time out.
