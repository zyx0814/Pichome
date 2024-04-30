[Client](Client.md) • [Server](Server.md) • Message • [Examples](Examples.md) • [Changelog](Changelog.md) • [Contributing](Contributing.md)

# Websocket: Messages

If option `return_obj` is set to `true` on [client](Client.md) or [server](Server.md),
the `receive()` method will return a Message instance instead of a string.

Available classes correspond to opcode;
* WebSocket\Message\Text
* WebSocket\Message\Binary
* WebSocket\Message\Ping
* WebSocket\Message\Pong
* WebSocket\Message\Close

Additionally;
* WebSocket\Message\Message - abstract base class for all messages above
* WebSocket\Message\Factory - Factory class to create Message instances

##  Message abstract class synopsis

```php
WebSocket\Message\Message {

    public __construct(string $payload = '');
    public __toString() : string;

    public getOpcode() : string;
    public getLength() : int;
    public getTimestamp() : DateTime;
    public getContent() : string;
    public setContent(string $payload = '') : void;
    public hasContent() : bool;
}
```

##  Factory class synopsis

```php
WebSocket\Message\Factory {

    public create(string $opcode, string $payload = '') : Message;
}
```

## Example

Receving a Message and echo some methods.

```php
$client = new WebSocket\Client('ws://echo.websocket.org/', ['return_obj' => true]);
$client->text('Hello WebSocket.org!');
// Echo return same message as sent
$message = $client->receive();
echo $message->getOpcode(); // -> "text"
echo $message->getLength(); // -> 20
echo $message->getContent(); // -> "Hello WebSocket.org!"
echo $message->hasContent(); // -> true
echo $message->getTimestamp()->format('H:i:s'); // -> 19:37:18
$client->close();
```
