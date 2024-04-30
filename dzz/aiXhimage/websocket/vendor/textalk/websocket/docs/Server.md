[Client](Client.md) • Server • [Message](Message.md) • [Examples](Examples.md) • [Changelog](Changelog.md) • [Contributing](Contributing.md)

# Websocket: Server

The library contains a rudimentary single stream/single thread server.
It internally supports Upgrade handshake and implicit close and ping/pong operations.

Note that it does **not** support threading or automatic association ot continuous client requests.
If you require this kind of server behavior, you need to build it on top of provided server implementation.

##  Class synopsis

```php
WebSocket\Server {

    public __construct(array $options = []);
    public __destruct();
    public __toString() : string;

    public accept() : bool;
    public text(string $payload) : void;
    public binary(string $payload) : void;
    public ping(string $payload = '') : void;
    public pong(string $payload = '') : void;
    public send(Message|string $payload, string $opcode = 'text', bool $masked = true) : void;
    public close(int $status = 1000, mixed $message = 'ttfn') : void;
    public receive() : Message|string|null;

    public getPort() : int;
    public getPath() : string;
    public getRequest() : array;
    public getHeader(string $header_name) : string|null;

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

### Simple receive-send operation

This example reads a single message from a client, and respond with the same message.

```php
$server = new WebSocket\Server();
$server->accept();
$message = $server->receive();
$server->text($message);
$server->close();
```

### Listening to clients

To continuously listen to incoming messages, you need to put the receive operation within a loop.
Note that these functions **always** throw exception on any failure, including recoverable failures such as connection time out.
By consuming exceptions, the code will re-connect the socket in next loop iteration.

```php
$server = new WebSocket\Server();
while ($server->accept()) {
    try {
        $message = $server->receive();
        // Act on received message
        // Break while loop to stop listening
    } catch (\WebSocket\ConnectionException $e) {
        // Possibly log errors
    }
}
$server->close();
```

### Filtering received messages

By default the `receive()` method return messages of 'text' and 'binary' opcode.
The filter option allows you to specify which message types to return.

```php
$server = new WebSocket\Server(['filter' => ['text']]);
$server->receive(); // only return 'text' messages

$server = new WebSocket\Server(['filter' => ['text', 'binary', 'ping', 'pong', 'close']]);
$server->receive(); // return all messages
```

### Sending messages

There are convenience methods to send messages with different opcodes.
```php
$server = new WebSocket\Server();

// Convenience methods
$server->text('A plain text message'); // Send an opcode=text message
$server->binary($binary_string); // Send an opcode=binary message
$server->ping(); // Send an opcode=ping frame
$server->pong(); // Send an unsolicited opcode=pong frame

// Generic send method
$server->send($payload); // Sent as masked opcode=text
$server->send($payload, 'binary'); // Sent as masked opcode=binary
$server->send($payload, 'binary', false); // Sent as unmasked opcode=binary
```

## Constructor options

The `$options` parameter in constructor accepts an associative array of options.

* `filter` - Array of opcodes to return on receive, default `['text', 'binary']`
* `fragment_size` - Maximum payload size. Default 4096 chars.
* `logger` - A [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger.
* `port` - The server port to listen to. Default 8000.
* `return_obj` - Return a [Message](Message.md) instance on receive, default false
* `timeout` - Time out in seconds. Default 5 seconds.

```php
$server = new WebSocket\Server([
    'filter' => ['text', 'binary', 'ping'], // Specify message types for receive() to return
    'logger' => $my_psr3_logger, // Attach a PSR3 compatible logger
    'port' => 9000, // Listening port
    'return_obj' => true, // Return Message instance rather than just text
    'timeout' => 60, // 1 minute time out
]);
```

## Exceptions

* `WebSocket\BadOpcodeException` - Thrown if provided opcode is invalid.
* `WebSocket\ConnectionException` - Thrown on any socket I/O failure.
* `WebSocket\TimeoutException` - Thrown when the socket experiences a time out.
