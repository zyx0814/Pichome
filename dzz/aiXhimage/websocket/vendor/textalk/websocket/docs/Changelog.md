[Client](Client.md) • [Server](Server.md) • [Message](Message.md) • [Examples](Examples.md) • Changelog • [Contributing](Contributing.md)

# Websocket: Changelog

## `v1.6`

 > PHP version `^7.4|^8.0`

### `1.6.3`

 * Fix issue with implicit default ports (@etrinh, @sirn-se)

### `1.6.2`

 * Fix issue where port was missing in socket uri (@sirn-se)

### `1.6.1`

 * Fix client path for http request (@simPod, @sirn-se)

### `1.6.0`
 * Connection separate from Client and Server (@sirn-se)
 * getPier() deprecated, replaced by getRemoteName() (@sirn-se)
 * Client accepts `Psr\Http\Message\UriInterface` as input for URI:s (@sirn-se)
 * Bad URI throws exception when Client is instanciated, previously when used (@sirn-se)
 * Preparations for multiple conection and listeners (@sirn-se)
 * Major internal refactoring (@sirn-se)

## `v1.5`

 > PHP version `^7.2|^8.0`

### `1.5.8`

 * Handle read error during handshake (@sirn-se)

### `1.5.7`

 * Large header block fix (@sirn-se)

### `1.5.6`

 * Add test for PHP 8.1 (@sirn-se)
 * Code standard (@sirn-se)

### `1.5.5`

 * Support for psr/log v2 and v3 (@simPod)
 * GitHub Actions replaces Travis (@sirn-se)

### `1.5.4`

 * Keep open connection on read timeout (@marcroberts)

### `1.5.3`

 * Fix for persistent connection (@sirn-se)

### `1.5.2`

 * Fix for getName() method (@sirn-se)

### `1.5.1`

 * Fix for persistent connections (@rmeisler)

### `1.5.0`

 * Convenience send methods; text(), binary(), ping(), pong() (@sirn-se)
 * Optional Message instance as receive() method return (@sirn-se)
 * Opcode filter for receive() method (@sirn-se)
 * Added PHP `8.0` support (@webpatser)
 * Dropped PHP `7.1` support (@sirn-se)
 * Fix for unordered fragmented messages (@sirn-se)
 * Improved error handling on stream calls (@sirn-se)
 * Various code re-write (@sirn-se)

## `v1.4`

 > PHP version `^7.1`

#### `1.4.3`

 * Solve stream closure/get meta conflict (@sirn-se)
 * Examples and documentation overhaul (@sirn-se)

#### `1.4.2`

 * Force stream close on read error (@sirn-se)
 * Authorization headers line feed (@sirn-se)
 * Documentation (@matias-pool, @sirn-se)

#### `1.4.1`

 * Ping/Pong, handled internally to avoid breaking fragmented messages (@nshmyrev, @sirn-se)
 * Fix for persistent connections (@rmeisler)
 * Fix opcode bitmask (@peterjah)

#### `1.4.0`

 * Dropped support of old PHP versions (@sirn-se)
 * Added PSR-3 Logging support (@sirn-se)
 * Persistent connection option (@slezakattack)
 * TimeoutException on connection time out (@slezakattack)

## `v1.3`

 > PHP version `^5.4` and `^7.0`

#### `1.3.1`

 * Allow control messages without payload (@Logioniz)
 * Error code in ConnectionException (@sirn-se)

#### `1.3.0`

 * Implements ping/pong frames (@pmccarren @Logioniz)
 * Close behaviour (@sirn-se)
 * Various fixes concerning connection handling (@sirn-se)
 * Overhaul of Composer, Travis and Coveralls setup, PSR code standard and unit tests (@sirn-se)

## `v1.2`

 > PHP version `^5.4` and `^7.0`

#### `1.2.0`

 * Adding stream context options (to set e.g. SSL `allow_self_signed`).

## `v1.1`

 > PHP version `^5.4` and `^7.0`

#### `1.1.2`

 * Fixed error message on broken frame.

#### `1.1.1`

 * Adding license information.

#### `1.1.0`

 * Supporting huge payloads.

## `v1.0`

 > PHP version `^5.4` and `^7.0`

#### `1.0.3`

 * Bugfix: Correcting address in error-message

#### `1.0.2`

 * Bugfix: Add port in request-header.

#### `1.0.1`

 * Fixing a bug from empty payloads.

#### `1.0.0`

 * Release as production ready.
 * Adding option to set/override headers.
 * Supporting basic authentication from user:pass in URL.

