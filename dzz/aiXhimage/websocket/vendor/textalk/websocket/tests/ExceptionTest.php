<?php

/**
 * Test case for Exceptions.
 */

declare(strict_types=1);

namespace WebSocket;

use PHPUnit\Framework\TestCase;
use Throwable;

class ExceptionTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testConnectionException(): void
    {
        try {
            throw new ConnectionException(
                'An error message',
                ConnectionException::EOF,
                ['test' => 'with data'],
                new TimeoutException(
                    'Nested exception',
                    ConnectionException::TIMED_OUT
                )
            );
        } catch (Throwable $e) {
        }

        $this->assertInstanceOf('WebSocket\ConnectionException', $e);
        $this->assertInstanceOf('WebSocket\Exception', $e);
        $this->assertInstanceOf('Exception', $e);
        $this->assertInstanceOf('Throwable', $e);
        $this->assertEquals('An error message', $e->getMessage());
        $this->assertEquals(1025, $e->getCode());
        $this->assertEquals(['test' => 'with data'], $e->getData());

        $p = $e->getPrevious();
        $this->assertInstanceOf('WebSocket\TimeoutException', $p);
        $this->assertInstanceOf('WebSocket\ConnectionException', $p);
        $this->assertEquals('Nested exception', $p->getMessage());
        $this->assertEquals(1024, $p->getCode());
        $this->assertEquals([], $p->getData());
    }
}
