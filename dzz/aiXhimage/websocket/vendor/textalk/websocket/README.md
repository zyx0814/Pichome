# Websocket Client and Server for PHP

[![Build Status](https://github.com/Textalk/websocket-php/actions/workflows/acceptance.yml/badge.svg)](https://github.com/Textalk/websocket-php/actions)
[![Coverage Status](https://coveralls.io/repos/github/Textalk/websocket-php/badge.svg?branch=master)](https://coveralls.io/github/Textalk/websocket-php)

This library contains WebSocket client and server for PHP.

The client and server provides methods for reading and writing to WebSocket streams.
It does not include convenience operations such as listeners and implicit error handling.

## Documentation

- [Client](docs/Client.md)
- [Server](docs/Server.md)
- [Examples](docs/Examples.md)
- [Changelog](docs/Changelog.md)
- [Contributing](docs/Contributing.md)

## Installing

Preferred way to install is with [Composer](https://getcomposer.org/).
```
composer require textalk/websocket
```

* Current version support PHP versions `^7.4|^8.0`.
* For PHP `7.2` and `7.3` support use version [`1.5`](https://github.com/Textalk/websocket-php/tree/1.5.0).
* For PHP `7.1` support use version [`1.4`](https://github.com/Textalk/websocket-php/tree/1.4.0).
* For PHP `^5.4` and `7.0` support use version [`1.3`](https://github.com/Textalk/websocket-php/tree/1.3.0).

## Client

The [client](docs/Client.md) can read and write on a WebSocket stream.
It internally supports Upgrade handshake and implicit close and ping/pong operations.

```php
$client = new WebSocket\Client("ws://echo.websocket.org/");
$client->text("Hello WebSocket.org!");
echo $client->receive();
$client->close();
```

## Server

The library contains a rudimentary single stream/single thread [server](docs/Server.md).
It internally supports Upgrade handshake and implicit close and ping/pong operations.

Note that it does **not** support threading or automatic association ot continuous client requests.
If you require this kind of server behavior, you need to build it on top of provided server implementation.

```php
$server = new WebSocket\Server();
$server->accept();
$message = $server->receive();
$server->text($message);
$server->close();
```

### License and Contributors

[ISC License](COPYING.md)

Fredrik Liljegren, Armen Baghumian Sankbarani, Ruslan Bekenev,
Joshua Thijssen, Simon Lipp, Quentin Bellus, Patrick McCarren, swmcdonnell,
Ignas Bernotas, Mark Herhold, Andreas Palm, Sören Jensen, pmaasz, Alexey Stavrov,
Michael Slezak, Pierre Seznec, rmeisler, Nickolay V. Shmyrev, Christoph Kempen,
Marc Roberts, Antonio Mora, Simon Podlipsky, etrinh.
