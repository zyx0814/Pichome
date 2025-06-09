[![Build Status](https://github.com/sirn-se/phrity-util-errorhandler/actions/workflows/acceptance.yml/badge.svg)](https://github.com/sirn-se/phrity-util-errorhandler/actions)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-util-errorhandler/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-util-errorhandler?branch=main)

# Error Handler utility

The PHP [error handling](https://www.php.net/manual/en/book.errorfunc.php) can be somewhat of a headache.
Typically an application uses a system level [error handler](https://www.php.net/manual/en/function.set-error-handler.php) and/or suppressing errors using the `@` prefix.
But those cases when your code need to act on triggered errors are more tricky.

This library provides two convenience methods to handle errors on code blocks, either by throwing exceptions or running callback code when an error occurs.

Current version supports PHP `^7.2|^8.0`.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/util-errorhandler
```

## The Error Handler

The class provides two main methods; `with()` and `withAll()`.
The difference is that `with()` will act immediately on an error and abort further code execution, while `withAll()` will attempt to execute the entire code block before acting on errors that occurred.

### Throwing ErrorException

```php
use Phrity\Util\ErrorHandler;

$handler = new ErrorHandler();
$result = $handler->with(function () {
    // Code to execute
    return $success_result;
});
$result = $handler->withAll(function () {
    // Code to execute
    return $success_result;
});
```
The examples above will run the callback code, but if an error occurs it will throw an [ErrorException](https://www.php.net/manual/en/class.errorexception.php).
Error message and severity will be that of the triggering error.
* `with()` will throw immediately when occured
* `withAll()` will throw when code is complete; if more than one error occurred, the first will be thrown

### Throwing specified Throwable

```php
use Phrity\Util\ErrorHandler;

$handler = new ErrorHandler();
$result = $handler->with(function () {
    // Code to execute
    return $success_result;
}, new RuntimeException('A specified error'));
$result = $handler->withAll(function () {
    // Code to execute
    return $success_result;
}, new RuntimeException('A specified error'));
```
The examples above will run the callback code, but if an error occurs it will throw provided Throwable.
The thrown Throwable will have an [ErrorException](https://www.php.net/manual/en/class.errorexception.php) attached as `$previous`.
* `with()` will throw immediately when occured
* `withAll()` will throw when code is complete; if more than one error occurred, the first will be thrown

### Using callback

```php
use Phrity\Util\ErrorHandler;

$handler = new ErrorHandler();
$result = $handler->with(function () {
    // Code to execute
    return $success_result;
}, function (ErrorException $error) {
    // Code to handle error
    return $error_result;
});
$result = $handler->withAll(function () {
    // Code to execute
    return $success_result;
}, function (array $errors, $success_result) {
    // Code to handle errors
    return $error_result;
});
```
The examples above will run the callback code, but if an error occurs it will call the error callback as well.
* `with()` will run the error callback immediately when error occured; error callback expects an ErrorException instance
* `withAll()` will run the error callback when code is complete; error callback expects an array of ErrorException and the returned result of code callback

### Filtering error types

Both `with()` and `withAll()` accepts error level(s) as last parameter.
```php
use Phrity\Util\ErrorHandler;

$handler = new ErrorHandler();
$result = $handler->with(function () {
    // Code to execute
    return $success_result;
}, null, E_USER_ERROR);
$result = $handler->withAll(function () {
    // Code to execute
    return $success_result;
}, null, E_USER_ERROR & E_USER_WARNING);
```
Any value or combination of values accepted by [set_error_handler](https://www.php.net/manual/en/function.set-error-handler.php) is usable.
Default is `E_ALL`. [List of constants](https://www.php.net/manual/en/errorfunc.constants.php).

### The global error handler

The class also has global `set()` and `restore()` methods.

```php
use Phrity\Util\ErrorHandler;

$handler = new ErrorHandler();
$handler->set(); // Throws ErrorException on error
$handler->set(new RuntimeException('A specified error')); // Throws provided Throwable on error
$handler->set(function (ErrorException $error) {
    // Code to handle errors
    return $error_result;
}); // Runs callback on error
$handler->restore(); // Restores error handler
```

###  Class synopsis

```php
Phrity\Util\ErrorHandler {

    /* Methods */
    public __construct()

    public with(callable $callback, mixed $handling = null, int $levels = E_ALL) : mixed
    public withAll(callable $callback, mixed $handling = null, int $levels = E_ALL) : mixed
    public set($handling = null, int $levels = E_ALL) : mixed
    public restore() : bool
}
```

## Versions

| Version | PHP | |
| --- | --- | --- |
| `1.0` | `^7.2\|^8.0` | Initial version |
