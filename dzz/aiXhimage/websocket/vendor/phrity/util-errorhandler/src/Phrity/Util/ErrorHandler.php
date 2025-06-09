<?php

/**
 * File for ErrorHandler utility class.
 * @package Phrity > Util > ErrorHandler
 */

namespace Phrity\Util;

use ErrorException;
use Throwable;

/**
 * ErrorHandler utility class.
 * Allows catching and resolving errors inline.
 */
class ErrorHandler
{
    /* ----------------- Public methods ---------------------------------------------- */

    /**
     * Set error handler to run until removed.
     * @param mixed $handling
     *   - If null, handler will throw ErrorException
     *   - If Throwable $t, throw $t with ErrorException attached as previous
     *   - If callable, will invoke callback with ErrorException as argument
     * @param int $levels Error levels to catch, all errors by default
     * @return mixed Previously registered error handler, if any
     */
    public function set($handling = null, int $levels = E_ALL)
    {
        return set_error_handler($this->getHandler($handling), $levels);
    }

    /**
     * Remove error handler.
     * @return bool True if removed
     */
    public function restore(): bool
    {
        return restore_error_handler();
    }

    /**
     * Run code with error handling, breaks on first encountered error.
     * @param callable $callback The code to run
     * @param mixed $handling
     *   - If null, handler will throw ErrorException
     *   - If Throwable $t, throw $t with ErrorException attached as previous
     *   - If callable, will invoke callback with ErrorException as argument
     * @param int $levels Error levels to catch, all errors by default
     * @return mixed Return what $callback returns, or what $handling retuns on error
     */
    public function with(callable $callback, $handling = null, int $levels = E_ALL)
    {
        $error = null;
        $result = null;
        try {
            $this->set(null, $levels);
            $result = $callback();
        } catch (ErrorException $e) {
            $error = $this->handle($handling, $e);
        }
        $this->restore();
        return $error ?: $result;
    }

    /**
     * Run code with error handling, comletes code before handling errors
     * @param callable $callback The code to run
     * @param mixed $handling
     *   - If null, handler will throw ErrorException
     *   - If Throwable $t, throw $t with ErrorException attached as previous
     *   - If callable, will invoke callback with ErrorException as argument
     * @param int $levels Error levels to catch, all errors by default
     * @return mixed Return what $callback returns, or what $handling retuns on error
     */
    public function withAll(callable $callback, $handling = null, int $levels = E_ALL)
    {
        $errors = [];
        $this->set(function (ErrorException $e) use (&$errors) {
            $errors[] = $e;
        }, $levels);
        $result = $callback();
        $error = empty($errors) ? null : $this->handle($handling, $errors, $result);
        $this->restore();
        return $error ?: $result;
    }

    /* ----------------- Private helpers --------------------------------------------- */

    // Get handler function
    private function getHandler($handling)
    {
        return function ($severity, $message, $file, $line) use ($handling) {
            $error = new ErrorException($message, 0, $severity, $file, $line);
            $this->handle($handling, $error);
        };
    }

    // Handle error according to $handlig type
    private function handle($handling, $error, $result = null)
    {
        if (is_callable($handling)) {
            return $handling($error, $result);
        }
        if (is_array($error)) {
            $error = array_shift($error);
        }
        if ($handling instanceof Throwable) {
            try {
                throw $error;
            } finally {
                throw $handling;
            }
        }
        throw $error;
    }
}
