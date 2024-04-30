# Testing

Unit tests with [PHPUnit](https://phpunit.readthedocs.io/).


## How to run

To run all test, run in console.

```
make test
```


## Continuous integration

GitHub Actions are run on PHP versions `7.4`, `8.0`, `8.1` and `8.2`.

Code coverage by [Coveralls](https://coveralls.io/github/Textalk/websocket-php).


## Test strategy

Test set up overloads various stream and socket functions,
and use "scripts" to define and mock input/output of these functions.

This set up negates the dependency on running servers,
and allow testing various errors that might occur.
