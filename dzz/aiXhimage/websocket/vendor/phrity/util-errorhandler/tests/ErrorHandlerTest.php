<?php

/**
 * File for ErrorHandler function tests.
 * @package Phrity > Util > ErrorHandler
 */

declare(strict_types=1);

namespace Phrity\Util;

use ErrorException;
use RuntimeException;
use Phrity\Util\ErrorHandler;
use PHPUnit\Framework\TestCase;

/**
 * ErrorHandler test class.
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testSetNull(): void
    {
        $handler = new ErrorHandler();
        $handler->set();

        // Verify exception
        try {
            trigger_error('An error');
        } catch (ErrorException $e) {
            $this->assertEquals('An error', $e->getMessage());
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals(E_USER_NOTICE, $e->getSeverity());
            $this->assertNull($e->getPrevious());
        }

        // Verify that exception is thrown
        $this->expectException('ErrorException');
        trigger_error('Another error');

        // Restore handler
        $this->assertTrue($handler->restore());
    }

    public function testSetThrowable(): void
    {
        $handler = new ErrorHandler();
        $handler->set(new RuntimeException('A provided exception', 23));

        // Verify exception
        try {
            trigger_error('An error');
        } catch (RuntimeException $e) {
            $this->assertEquals('A provided exception', $e->getMessage());
            $this->assertEquals(23, $e->getCode());
            $this->assertNotNull($e->getPrevious());
            $prev = $e->getPrevious();
            $this->assertEquals('An error', $prev->getMessage());
            $this->assertEquals(0, $prev->getCode());
            $this->assertEquals(E_USER_NOTICE, $prev->getSeverity());
            $this->assertNull($prev->getPrevious());
        }

        // Verify that exception is thrown
        $this->expectException('RuntimeException');
        trigger_error('Another error');

        // Restore handler
        $this->assertTrue($handler->restore());
    }

    public function testSetCallback(): void
    {
        $handler = new ErrorHandler();
        $result = null;
        $handler->set(function ($error) use (&$result) {
            $result = [
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'severity' => $error->getSeverity(),
            ];
        });

        // Verify exception
        trigger_error('An error');
        $this->assertEquals([
            'message' => 'An error',
            'code' => 0,
            'severity' => E_USER_NOTICE,
        ], $result);

        // Restore handler
        $this->assertTrue($handler->restore());
    }

    public function testWithNull(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No exception
        $result = $handler->with(function () {
            return 'Code success';
        });
        $this->assertEquals('Code success', $result);

        // Verify exception
        try {
            $result = $handler->with(function () use (&$check) {
                trigger_error('An error');
                $check = true;
                return 'Code success';
            });
        } catch (ErrorException $e) {
            $this->assertEquals('An error', $e->getMessage());
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals(E_USER_NOTICE, $e->getSeverity());
            $this->assertNull($e->getPrevious());
        }
        $this->assertFalse($check);

         // Verify that exception is thrown
        $this->expectException('ErrorException');
        $result = $handler->with(function () {
            trigger_error('An error');
            return 'Code success';
        });
    }

    public function testWithThrowable(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No exception
        $result = $handler->with(function () {
            return 'Code success';
        });
        $this->assertEquals('Code success', $result);

        // Verify exception
        try {
            $result = $handler->with(function () use (&$check) {
                trigger_error('An error');
                $check = true;
                return 'Code success';
            }, new RuntimeException('A provided exception', 23));
        } catch (RuntimeException $e) {
            $this->assertEquals('A provided exception', $e->getMessage());
            $this->assertEquals(23, $e->getCode());
            $this->assertNotNull($e->getPrevious());
            $prev = $e->getPrevious();
            $this->assertEquals('An error', $prev->getMessage());
            $this->assertEquals(0, $prev->getCode());
            $this->assertEquals(E_USER_NOTICE, $prev->getSeverity());
            $this->assertNull($prev->getPrevious());
        }
        $this->assertFalse($check);

         // Verify that exception is thrown
        $this->expectException('RuntimeException');
        $result = $handler->with(function () {
            trigger_error('An error');
            return 'Code success';
        }, new RuntimeException('A provided exception', 23));
    }

    public function testWithCallback(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No error invoked
        $result = $handler->with(function () {
            return 'Code success';
        }, function ($error) {
            return $error;
        });
        $this->assertEquals('Code success', $result);

        // An error is invoked
        $result = $handler->with(function () use (&$check) {
            trigger_error('An error');
            $check = true;
            return 'Code success';
        }, function ($error) {
            return $error;
        });
        $this->assertFalse($check);

        $this->assertEquals('An error', $result->getMessage());
        $this->assertEquals(0, $result->getCode());
        $this->assertEquals(E_USER_NOTICE, $result->getSeverity());
        $this->assertNull($result->getPrevious());
    }

    public function testWithAllNull(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No error invoked
        $result = $handler->withAll(function () {
            return 'Code success';
        });
        $this->assertEquals('Code success', $result);

        // Verify exception
        try {
            $result = $handler->withAll(function () use (&$check) {
                trigger_error('An error');
                $check = true;
                return 'Code success';
            });
        } catch (ErrorException $e) {
            $this->assertEquals('An error', $e->getMessage());
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals(E_USER_NOTICE, $e->getSeverity());
            $this->assertNull($e->getPrevious());
        }
        $this->assertTrue($check);

         // Verify that exception is thrown
        $this->expectException('ErrorException');
        $result = $handler->withAll(function () {
            trigger_error('An error');
            return 'Code success';
        });
    }

    public function testWithAllThrowable(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No exception
        $result = $handler->withAll(function () {
            return 'Code success';
        });
        $this->assertEquals('Code success', $result);

        // Verify exception
        try {
            $result = $handler->withAll(function () use (&$check) {
                trigger_error('An error');
                $check = true;
                return 'Code success';
            }, new RuntimeException('A provided exception', 23));
        } catch (RuntimeException $e) {
            $this->assertEquals('A provided exception', $e->getMessage());
            $this->assertEquals(23, $e->getCode());
            $this->assertNotNull($e->getPrevious());
            $prev = $e->getPrevious();
            $this->assertEquals('An error', $prev->getMessage());
            $this->assertEquals(0, $prev->getCode());
            $this->assertEquals(E_USER_NOTICE, $prev->getSeverity());
            $this->assertNull($prev->getPrevious());
        }
        $this->assertTrue($check);

         // Verify that exception is thrown
        $this->expectException('RuntimeException');
        $result = $handler->withAll(function () {
            trigger_error('An error');
            return 'Code success';
        }, new RuntimeException('A provided exception', 23));
    }

    public function testWithAllCallback(): void
    {
        $handler = new ErrorHandler();
        $check = false;

        // No error invoked
        $result = $handler->withAll(function () {
            return 'Code success';
        }, function ($error, $result) {
            return $error;
        });
        $this->assertEquals('Code success', $result);

        // An error is invoked
        $result = $handler->withAll(function () use (&$check) {
            trigger_error('An error');
            trigger_error('Another error', E_USER_WARNING);
            $check = true;
            return 'Code success';
        }, function ($errors, $result) {
            return ['errors' => $errors, 'result' => $result];
        });
        $this->assertTrue($check);

        $this->assertEquals('Code success', $result['result']);
        $this->assertEquals('An error', $result['errors'][0]->getMessage());
        $this->assertEquals(0, $result['errors'][0]->getCode());
        $this->assertEquals(E_USER_NOTICE, $result['errors'][0]->getSeverity());
        $this->assertNull($result['errors'][0]->getPrevious());
        $this->assertEquals('Another error', $result['errors'][1]->getMessage());
        $this->assertEquals(0, $result['errors'][1]->getCode());
        $this->assertEquals(E_USER_WARNING, $result['errors'][1]->getSeverity());
        $this->assertNull($result['errors'][1]->getPrevious());
    }
}
