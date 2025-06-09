<?php

/**
 * Test case for Message subsection.
 */

declare(strict_types=1);

namespace WebSocket;

use PHPUnit\Framework\TestCase;
use WebSocket\Message\Factory;
use WebSocket\Message\Text;

class MessageTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testFactory(): void
    {
        $factory = new Factory();
        $message = $factory->create('text', 'Some content');
        $this->assertInstanceOf('WebSocket\Message\Text', $message);
        $message = $factory->create('binary', 'Some content');
        $this->assertInstanceOf('WebSocket\Message\Binary', $message);
        $message = $factory->create('ping', 'Some content');
        $this->assertInstanceOf('WebSocket\Message\Ping', $message);
        $message = $factory->create('pong', 'Some content');
        $this->assertInstanceOf('WebSocket\Message\Pong', $message);
        $message = $factory->create('close', 'Some content');
        $this->assertInstanceOf('WebSocket\Message\Close', $message);
    }

    public function testMessage()
    {
        $message = new Text('Some content');
        $this->assertInstanceOf('WebSocket\Message\Message', $message);
        $this->assertInstanceOf('WebSocket\Message\Text', $message);
        $this->assertEquals('Some content', $message->getContent());
        $this->assertEquals('text', $message->getOpcode());
        $this->assertEquals(12, $message->getLength());
        $this->assertTrue($message->hasContent());
        $this->assertInstanceOf('DateTime', $message->getTimestamp());
        $message->setContent('');
        $this->assertEquals(0, $message->getLength());
        $this->assertFalse($message->hasContent());
        $this->assertEquals('WebSocket\Message\Text', "{$message}");
    }

    public function testBadOpcode()
    {
        $factory = new Factory();
        $this->expectException('WebSocket\BadOpcodeException');
        $this->expectExceptionMessage("Invalid opcode 'invalid' provided");
        $message = $factory->create('invalid', 'Some content');
    }
}
