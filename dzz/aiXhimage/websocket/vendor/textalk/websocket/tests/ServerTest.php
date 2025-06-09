<?php

/**
 * Test case for Server.
 * Note that this test is performed by mocking socket/stream calls.
 */

declare(strict_types=1);

namespace WebSocket;

use ErrorException;
use Phrity\Util\ErrorHandler;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testServerMasked(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertEquals(8000, $server->getPort());
        $this->assertEquals('/my/mock/path', $server->getPath());
        $this->assertTrue($server->isConnected());
        $this->assertEquals(4096, $server->getFragmentSize());
        $this->assertNull($server->getCloseStatus());
        $this->assertEquals([
            'GET /my/mock/path HTTP/1.1',
            'host: localhost:8000',
            'user-agent: websocket-client-php',
            'connection: Upgrade',
            'upgrade: websocket',
            'sec-websocket-key: cktLWXhUdDQ2OXF0ZCFqOQ==',
            'sec-websocket-version: 13',
            '',
            '',
        ], $server->getRequest());
        $this->assertEquals('websocket-client-php', $server->getHeader('USER-AGENT'));
        $this->assertNull($server->getHeader('no such header'));
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('send-receive', $this);
        $server->send('Sending a message');
        $message = $server->receive();
        $this->assertEquals('Receiving a message', $message);
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertNull($server->getCloseStatus());
        $this->assertEquals('text', $server->getLastOpcode());

        MockSocket::initialize('server.close', $this);
        $server->close();
        $this->assertFalse($server->isConnected());
        $this->assertEquals(1000, $server->getCloseStatus());
        $this->assertTrue(MockSocket::isEmpty());

        $server->close(); // Already closed
    }

    public function testDestruct(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();

        MockSocket::initialize('server.accept-destruct', $this);
        $server->accept();
        $message = $server->receive();
    }

    public function testServerWithTimeout(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server(['timeout' => 300]);
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept-timeout', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPayload128(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        $payload = file_get_contents(__DIR__ . '/mock/payload.128.txt');

        MockSocket::initialize('send-receive-128', $this);
        $server->send($payload, 'text', false);
        $message = $server->receive();
        $this->assertEquals($payload, $message);
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPayload65536(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        $payload = file_get_contents(__DIR__ . '/mock/payload.65536.txt');
        $server->setFragmentSize(65540);

        MockSocket::initialize('send-receive-65536', $this);
        $server->send($payload, 'text', false);
        $message = $server->receive();
        $this->assertEquals($payload, $message);
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testMultiFragment(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('send-receive-multi-fragment', $this);
        $server->setFragmentSize(8);
        $server->send('Multi fragment test');
        $message = $server->receive();
        $this->assertEquals('Multi fragment test', $message);
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testPingPong(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('ping-pong', $this);
        $server->send('Server ping', 'ping');
        $server->send('', 'ping');
        $message = $server->receive();
        $this->assertEquals('Receiving a message', $message);
        $this->assertEquals('text', $server->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testRemoteClose(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('close-remote', $this);

        $message = $server->receive();
        $this->assertEquals('', $message);

        $this->assertTrue(MockSocket::isEmpty());
        $this->assertFalse($server->isConnected());
        $this->assertEquals(17260, $server->getCloseStatus());
        $this->assertNull($server->getLastOpcode());
    }

    public function testSetTimeout(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('config-timeout', $this);
        $server->setTimeout(300);
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testFailedSocketServer(): void
    {
        MockSocket::initialize('server.construct-failed-socket-server', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not open listening socket:');
        $server = new Server(['port' => 9999]);
    }

    public function testFailedSocketServerWithError(): void
    {
        MockSocket::initialize('server.construct-error-socket-server', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not open listening socket:');
        $server = new Server(['port' => 9999]);
    }

    public function testFailedConnect(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();

        MockSocket::initialize('server.accept-failed-connect', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Server failed to connect');
        $server->send('Connect');
    }

    public function testFailedConnectWithError(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();

        MockSocket::initialize('server.accept-error-connect', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Server failed to connect');
        $server->send('Connect');
    }

    public function testFailedConnectTimeout(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server(['timeout' => 300]);

        MockSocket::initialize('server.accept-failed-connect', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Server failed to connect');
        $server->send('Connect');
    }

    public function testFailedHttp(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept-failed-http', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('No GET in request');
        $server->send('Connect');
    }

    public function testFailedWsKey(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept-failed-ws-key', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Client had no Key in upgrade request');
        $server->send('Connect');
    }

    public function testSendBadOpcode(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->expectException('WebSocket\BadOpcodeException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Bad opcode \'bad\'.  Try \'text\' or \'binary\'.');
        $server->send('Bad Opcode', 'bad');
    }

    public function testRecieveBadOpcode(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('receive-bad-opcode', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1026);
        $this->expectExceptionMessage('Bad opcode in websocket frame: 12');
        $message = $server->receive();
    }

    public function testBrokenWrite(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('send-broken-write', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1025);
        $this->expectExceptionMessage('Could only write 18 out of 22 bytes.');
        $server->send('Failing to write');
    }

    public function testFailedWrite(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('send-failed-write', $this);
        $this->expectException('WebSocket\TimeoutException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Failed to write 22 bytes.');
        $server->send('Failing to write');
    }

    public function testBrokenRead(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('receive-broken-read', $this);
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(1025);
        $this->expectExceptionMessage('Broken frame, read 0 of stated 2 bytes.');
        $server->receive();
    }

    public function testEmptyRead(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('receive-empty-read', $this);
        $this->expectException('WebSocket\TimeoutException');
        $this->expectExceptionCode(1024);
        $this->expectExceptionMessage('Empty read; connection dead?');
        $server->receive();
    }

    public function testFrameFragmentation(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server(['filter' => ['text', 'binary', 'pong', 'close']]);
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('receive-fragmentation', $this);
        $message = $server->receive();
        $this->assertEquals('Server ping', $message);
        $this->assertEquals('pong', $server->getLastOpcode());
        $message = $server->receive();
        $this->assertEquals('Multi fragment test', $message);
        $this->assertEquals('text', $server->getLastOpcode());
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('close-remote', $this);
        $message = $server->receive();
        $this->assertEquals('Closing', $message);
        $this->assertTrue(MockSocket::isEmpty());
        $this->assertFalse($server->isConnected());
        $this->assertEquals(17260, $server->getCloseStatus());
        $this->assertEquals('close', $server->getLastOpcode());
    }

    public function testMessageFragmentation(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server(['filter' => ['text', 'binary', 'pong', 'close'], 'return_obj' => true]);
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        MockSocket::initialize('receive-fragmentation', $this);
        $message = $server->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Pong', $message);
        $this->assertEquals('Server ping', $message->getContent());
        $this->assertEquals('pong', $message->getOpcode());
        $message = $server->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Text', $message);
        $this->assertEquals('Multi fragment test', $message->getContent());
        $this->assertEquals('text', $message->getOpcode());
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('close-remote', $this);
        $message = $server->receive();
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Close', $message);
        $this->assertEquals('Closing', $message->getContent());
        $this->assertEquals('close', $message->getOpcode());
    }

    public function testConvenicanceMethods(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertNull($server->getName());
        $this->assertNull($server->getRemoteName());
        $this->assertEquals('WebSocket\Server(closed)', "{$server}");
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->text('Connect');
        MockSocket::initialize('send-convenicance', $this);
        $server->binary(base64_encode('Binary content'));
        $server->ping();
        $server->pong();
        $this->assertEquals('127.0.0.1:12345', $server->getName());
        $this->assertEquals('127.0.0.1:8000', $server->getRemoteName());
        $this->assertEquals('WebSocket\Server(127.0.0.1:12345)', "{$server}");
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testUnconnectedServer(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertFalse($server->isConnected());
        $server->setTimeout(30);
        $server->close();
        $this->assertFalse($server->isConnected());
        $this->assertNull($server->getName());
        $this->assertNull($server->getRemoteName());
        $this->assertNull($server->getCloseStatus());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testFailedHandshake(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.accept-failed-handshake', $this);
        $server->accept();
        $this->expectException('WebSocket\ConnectionException');
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Could not read from stream');
        $server->send('Connect');
        $this->assertFalse($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testServerDisconnect(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());
        MockSocket::initialize('server.accept', $this);
        $server->accept();
        $server->send('Connect');
        $this->assertTrue($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());

        MockSocket::initialize('server.disconnect', $this);
        $server->disconnect();
        $this->assertFalse($server->isConnected());
        $this->assertTrue(MockSocket::isEmpty());
    }

    public function testDeprecated(): void
    {
        MockSocket::initialize('server.construct', $this);
        $server = new Server();
        $this->assertTrue(MockSocket::isEmpty());
        (new ErrorHandler())->withAll(function () use ($server) {
            $this->assertNull($server->getPier());
        }, function ($exceptions, $result) {
            $this->assertEquals(
                'getPier() is deprecated and will be removed in future version. Use getRemoteName() instead.',
                $exceptions[0]->getMessage()
            );
        }, E_USER_DEPRECATED);
    }
}
