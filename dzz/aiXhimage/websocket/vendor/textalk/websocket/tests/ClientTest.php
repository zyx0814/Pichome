<?php

/**
 * Test case for Client.
 * Note that this test is performed by mocking socket/stream calls.
 */

declare(strict_types=1);

namespace WebSocket;

use ErrorException;
use Phrity\Net\Uri;
use Phrity\Util\ErrorHandler;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testClientMasked(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertEquals(4096, $client->getFragmentSize());

        MockSocket::initialize('send-receive', $this);
        $client->send('Sending a message');
        $message = $client->receive();
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertEquals('text', $client->getLastOpcode());

        MockSocket::initialize('client.close', $this);
        $this->assertTrue($client->isConnected());
        $this->assertNull($client->getCloseStatus());

        $client->close();
        $this->assertFalse($client->isConnected());
        $this->assertEquals(1000, $client->getCloseStatus());

        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testDestruct(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('client.destruct', $this);
    }

    public function testClienExtendedUrl(): void
    {
        MockSocket::initialize('client.connect-extended', $this);
        $client = new Client('ws://localhost:8000/my/mock/path?my_query=yes#my_fragment');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientNoPath(): void
    {
        MockSocket::initialize('client.connect-root', $this);
        $client = new Client('ws://localhost:8000');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientRelativePath(): void
    {
        MockSocket::initialize('client.connect', $this);
        $uri = new Uri('ws://localhost:8000');
        $uri = $uri->withPath('my/mock/path');
        $client = new Client($uri);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientWsDefaultPort(): void
    {
        MockSocket::initialize('client.connect-default-port-ws', $this);
        $uri = new Uri('ws://localhost');
        $uri = $uri->withPath('my/mock/path');
        $client = new Client($uri);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientWssDefaultPort(): void
    {
        MockSocket::initialize('client.connect-default-port-wss', $this);
        $uri = new Uri('wss://localhost');
        $uri = $uri->withPath('my/mock/path');
        $client = new Client($uri);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientWithTimeout(): void
    {
        MockSocket::initialize('client.connect-timeout', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', ['timeout' => 300]);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientWithContext(): void
    {
        MockSocket::initialize('client.connect-context', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', ['context' => '@mock-stream-context']);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testClientAuthed(): void
    {
        MockSocket::initialize('client.connect-authed', $this);
        $client = new Client('wss://usename:password@localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testWithHeaders(): void
    {
        MockSocket::initialize('client.connect-headers', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', [
            'origin' => 'Origin header',
            'headers' => ['Generic header' => 'Generic content'],
        ]);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPayload128(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        $payload = file_get_contents(__DIR__ . '/mock/payload.128.txt');

        MockSocket::initialize('send-receive-128', $this);
        $client->send($payload, 'text', false);
        $message = $client->receive();
        $this->assertEquals($payload, $message);
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPayload65536(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        $payload = file_get_contents(__DIR__ . '/mock/payload.65536.txt');
        $client->setFragmentSize(65540);

        MockSocket::initialize('send-receive-65536', $this);
        $client->send($payload, 'text', false);
        $message = $client->receive();
        $this->assertEquals($payload, $message);
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertEquals(65540, $client->getFragmentSize());
    }

    public function testMultiFragment(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('send-receive-multi-fragment', $this);
        $client->setFragmentSize(8);
        $client->send('Multi fragment test');
        $message = $client->receive();
        $this->assertEquals('Multi fragment test', $message);
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertEquals(8, $client->getFragmentSize());
    }

    public function testPingPong(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('ping-pong', $this);
        $client->send('Server ping', 'ping');
        $client->send('', 'ping');
        $message = $client->receive();
        $this->assertEquals('Receiving a message', $message);
        $this->assertEquals('text', $client->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testRemoteClose(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('close-remote', $this);

        $message = $client->receive();
        $this->assertNull($message);

        $this->assertFalse($client->isConnected());
        $this->assertEquals(17260, $client->getCloseStatus());
        $this->assertNull($client->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testSetTimeout(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('config-timeout', $this);
        $client->setTimeout(300);
        $this->assertTrue($client->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testReconnect(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('client.close', $this);
        $this->assertTrue($client->isConnected());
        $this->assertNull($client->getCloseStatus());
        $client->close();
        $this->assertFalse($client->isConnected());
        $this->assertEquals(1000, $client->getCloseStatus());
        $this->assertNull($client->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('client.reconnect', $this);
        $message = $client->receive();
        $this->assertTrue($client->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPersistentConnection(): void
    {
        MockSocket::initialize('client.connect-persistent', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', ['persistent' => true]);
        $client->send('Connect');
        $client->disconnect();
        $this->assertFalse($client->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testFailedPersistentConnection(): void
    {
        MockSocket::initialize('client.connect-persistent-failure', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', ['persistent' => true]);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionMessage('Could not resolve stream pointer position');
        $client->send('Connect');
    }

    public function testBadScheme(): void
    {
        MockSocket::initialize('client.connect', $this);
        $this->expectException('WebSocket\BadUriException');
        $this->expectExceptionMessage("Invalid URI scheme, must be 'ws' or 'wss'.");
        $client = new Client('bad://localhost:8000/my/mock/path');
    }

    public function testBadUri(): void
    {
        MockSocket::initialize('client.connect', $this);
        $this->expectException('WebSocket\BadUriException');
        $this->expectExceptionMessage("Invalid URI '--:this is not an uri:--' provided.");
        $client = new Client('--:this is not an uri:--');
    }

    public function testInvalidUriType(): void
    {
        MockSocket::initialize('client.connect', $this);
        $this->expectException('WebSocket\BadUriException');
        $this->expectExceptionMessage("Provided URI must be a UriInterface or string.");
        $client = new Client([]);
    }

    public function testUriInterface(): void
    {
        MockSocket::initialize('client.connect', $this);
        $uri = new Uri('ws://localhost:8000/my/mock/path');
        $client = new Client($uri);
        $client->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testBadStreamContext(): void
    {
        MockSocket::initialize('client.connect-bad-context', $this);
        $client = new Client('ws://localhost:8000/my/mock/path', ['context' => 'BAD']);
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Stream context in $options[\'context\'] isn\'t a valid context');
        $client->send('Connect');
    }

    public function testFailedConnection(): void
    {
        MockSocket::initialize('client.connect-failed', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not open socket to "localhost:8000"');
        $client->send('Connect');
    }

    public function testFailedConnectionWithError(): void
    {
        MockSocket::initialize('client.connect-error', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not open socket to "localhost:8000"');
        $client->send('Connect');
    }

    public function testBadStreamConnection(): void
    {
        MockSocket::initialize('client.connect-bad-stream', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Invalid stream on "localhost:8000"');
        $client->send('Connect');
    }

    public function testHandshakeFailure(): void
    {
        MockSocket::initialize('client.connect-handshake-failure', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Client handshake error');
        $client->send('Connect');
    }

    public function testInvalidUpgrade(): void
    {
        MockSocket::initialize('client.connect-invalid-upgrade', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Connection to \'ws://localhost:8000/my/mock/path\' failed');
        $client->send('Connect');
    }

    public function testInvalidKey(): void
    {
        MockSocket::initialize('client.connect-invalid-key', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Server sent bad upgrade response');
        $client->send('Connect');
    }

    public function testSendBadOpcode(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');

        MockSocket::initialize('send-bad-opcode', $this);
        $this->expectException('WebSocket\BadOpcodeException');
        $this->expectExceptionMessage('Bad opcode \'bad\'.  Try \'text\' or \'binary\'.');
        $client->send('Bad Opcode', 'bad');
    }

    public function testRecieveBadOpcode(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('receive-bad-opcode', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1026);
        $this->expectExceptionMessage('Bad opcode in websocket frame: 12');
        $message = $client->receive();
    }

    public function testBrokenWrite(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('send-broken-write', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1025);
        $this->expectExceptionMessage('Could only write 18 out of 22 bytes.');
        $client->send('Failing to write');
    }

    public function testFailedWrite(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('send-failed-write', $this);
        $this->expectException('WebSocket\TimeoutException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Failed to write 22 bytes.');
        $client->send('Failing to write');
    }

    public function testBrokenRead(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('receive-broken-read', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1025);
        $this->expectExceptionMessage('Broken frame, read 0 of stated 2 bytes.');
        $client->receive();
    }

    public function testHandshakeError(): void
    {
        MockSocket::initialize('client.connect-handshake-error', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Client handshake error');
        $client->send('Connect');
    }

    public function testReadTimeout(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('receive-client-timeout', $this);
        $this->expectException('WebSocket\TimeoutException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Client read timeout');
        $client->receive();
    }

    public function testEmptyRead(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $client->send('Connect');
        MockSocket::initialize('receive-empty-read', $this);
        $this->expectException('WebSocket\TimeoutException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Empty read; connection dead?');
        $client->receive();
    }

    public function testFrameFragmentation(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client(
            'ws://localhost:8000/my/mock/path',
            ['filter' => ['text', 'binary', 'pong', 'close']]
        );
        $client->send('Connect');
        MockSocket::initialize('receive-fragmentation', $this);
        $message = $client->receive();
        $this->assertEquals('Server ping', $message);
        $this->assertEquals('pong', $client->getLastOpcode());
        $message = $client->receive();
        $this->assertEquals('Multi fragment test', $message);
        $this->assertEquals('text', $client->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('close-remote', $this);
        $message = $client->receive();
        $this->assertEquals('Closing', $message);
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertFalse($client->isConnected());
        $this->assertEquals(17260, $client->getCloseStatus());
        $this->assertEquals('close', $client->getLastOpcode());
    }

    public function testMessageFragmentation(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client(
            'ws://localhost:8000/my/mock/path',
            ['filter' => ['text', 'binary', 'pong', 'close'], 'return_obj' => true]
        );
        $client->send('Connect');
        MockSocket::initialize('receive-fragmentation', $this);
        $message = $client->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Pong', $message);
        $this->assertEquals('Server ping', $message->getContent());
        $this->assertEquals('pong', $message->getOpcode());
        $message = $client->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Text', $message);
        $this->assertEquals('Multi fragment test', $message->getContent());
        $this->assertEquals('text', $message->getOpcode());
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('close-remote', $this);
        $message = $client->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Close', $message);
        $this->assertEquals('Closing', $message->getContent());
        $this->assertEquals('close', $message->getOpcode());
    }

    public function testConvenicanceMethods(): void
    {
        MockSocket::initialize('client.connect', $this);
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->assertNull($client->getName());
        $this->assertNull($client->getRemoteName());
        $this->assertEquals('WebSocket\Client(closed)', "{$client}");
        $client->text('Connect');
        MockSocket::initialize('send-convenicance', $this);
        $client->binary(base64_encode('Binary content'));
        $client->ping();
        $client->pong();
        $this->assertEquals('127.0.0.1:12345', $client->getName());
        $this->assertEquals('127.0.0.1:8000', $client->getRemoteName());
        $this->assertEquals('WebSocket\Client(127.0.0.1:12345)', "{$client}");
    }

    public function testUnconnectedClient(): void
    {
        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->assertFalse($client->isConnected());
        $client->setTimeout(30);
        $client->close();
        $this->assertFalse($client->isConnected());
        $this->assertNull($client->getName());
        $this->assertNull($client->getRemoteName());
        $this->assertNull($client->getCloseStatus());
    }

    public function testDeprecated(): void
    {
        $client = new Client('ws://localhost:8000/my/mock/path');
        (new ErrorHandler())->withAll(function () use ($client) {
            $this->assertNull($client->getPier());
        }, function ($exceptions, $result) {
            $this->assertEquals(
                'getPier() is deprecated and will be removed in future version. Use getRemoteName() instead.',
                $exceptions[0]->getMessage()
            );
        }, E_USER_DEPRECATED);
    }
}
