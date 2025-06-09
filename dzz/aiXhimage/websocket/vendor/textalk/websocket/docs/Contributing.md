[Client](Client.md) • [Server](Server.md) • [Message](Message.md) • [Examples](Examples.md) • [Changelog](Changelog.md) • Contributing

# Websocket: Contributing

Everyone is welcome to help out!
But to keep this project sustainable, please ensure your contribution respects the requirements below.

## PR Requirements

Requirements on pull requests;
* All tests **MUST** pass.
* Code coverage **MUST** remain at 100%.
* Code **MUST** adhere to PSR-1 and PSR-12 code standards.

Base your patch on corresponding version branch, and target that version branch in your pull request.

* `v1.6-master` current version
* `v1.5-master` previous version, bug fixes only
* Older versions should not be target of pull requests


## Dependency management

Install or update dependencies using [Composer](https://getcomposer.org/).

```
# Install dependencies
make install

# Update dependencies
make update
```

## Code standard

This project uses [PSR-1](https://www.php-fig.org/psr/psr-1/) and [PSR-12](https://www.php-fig.org/psr/psr-12/) code standards.
```
# Check code standard adherence
make cs-check
```

## Unit testing

Unit tests with [PHPUnit](https://phpunit.readthedocs.io/), coverage with [Coveralls](https://github.com/php-coveralls/php-coveralls)
```
# Run unit tests
make test

# Create coverage
make coverage
```
