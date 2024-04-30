[Client](Client.md) • [Server](Server.md) • [Message](Message.md) • Examples • [Changelog](Changelog.md) • [Contributing](Contributing.md)

# Websocket: Examples

Here are some examples on how to use the WebSocket library.

##  Echo logger

In dev environment (as in having run composer to include dev dependencies) you have
access to a simple echo logger that print out information synchronously.

This is usable for debugging. For production, use a proper logger.

```php
namespace WebSocket;

$logger = new EchoLogger();

$client = new Client('ws://echo.websocket.org/');
$client->setLogger($logger);

$server = new Server();
$server->setLogger($logger);
```

An example of server output;
```
info     | Server listening to port 8000 []
debug    | Wrote 129 of 129 bytes. []
info     | Server connected to port 8000 []
info     | Received 'text' message []
debug    | Wrote 9 of 9 bytes. []
info     | Sent 'text' message []
debug    | Received 'close', status: 1000. []
debug    | Wrote 32 of 32 bytes. []
info     | Sent 'close' message []
info     | Received 'close' message []
```

## The `send` client

Source: [examples/send.php](../examples/send.php)

A simple, single send/receive client.

Example use:
```
php examples/send.php --opcode text "A text message" // Send a text message to localhost
php examples/send.php --opcode ping "ping it" // Send a ping message to localhost
php examples/send.php --uri ws://echo.websocket.org "A text message" // Send a text message to echo.websocket.org
php examples/send.php --opcode text --debug "A text message" // Use runtime debugging
```

## The `echoserver` server

Source: [examples/echoserver.php](../examples/echoserver.php)

A simple server that responds to recevied commands.

Example use:
```
php examples/echoserver.php // Run with default settings
php examples/echoserver.php --port 8080 // Listen on port 8080
php examples/echoserver.php --debug //  Use runtime debugging
```

These strings can be sent as message to trigger server to perform actions;
* `auth` -  Server will respond with auth header if provided by client
* `close` -  Server will close current connection
* `exit` - Server will close all active connections
* `headers` - Server will respond with all headers provided by client
* `ping` - Server will send a ping message
* `pong` - Server will send a pong message
* `stop` - Server will stop listening
* For other sent strings, server will respond with the same strings

## The `random` client

Source: [examples/random_client.php](../examples/random_client.php)

The random client will use random options and continuously send/receive random messages.

Example use:
```
php examples/random_client.php --uri ws://echo.websocket.org // Connect to echo.websocket.org
php examples/random_client.php --timeout 5 --fragment_size 16 // Specify settings
php examples/random_client.php --debug //  Use runtime debugging
```

## The `random` server

Source: [examples/random_server.php](../examples/random_server.php)

The random server will use random options and continuously send/receive random messages.

Example use:
```
php examples/random_server.php --port 8080 // // Listen on port 8080
php examples/random_server.php --timeout 5 --fragment_size 16 // Specify settings
php examples/random_server.php --debug //  Use runtime debugging
```
