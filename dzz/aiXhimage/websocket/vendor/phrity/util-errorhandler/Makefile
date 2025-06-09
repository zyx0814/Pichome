# Default
all: deps-install


# DEPENDENCY MANAGEMENT

# Updates dependencies according to lock file
deps-install: composer.phar
	./composer.phar --no-interaction install

# Updates dependencies according to json file
deps-update: composer.phar
	./composer.phar self-update
	./composer.phar --no-interaction update


# TESTS AND REPORTS

# Code standard check
cs-check: composer.lock
	./vendor/bin/phpcs --standard=PSR1,PSR12 --encoding=UTF-8 --report=full --colors src tests

# Run tests
test: composer.lock
	./vendor/bin/phpunit

# Run tests with clover coverage report
coverage: composer.lock
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover build/logs/clover.xml
	./vendor/bin/php-coveralls -v


# INITIAL INSTALL

# Ensures composer is installed
composer.phar:
	curl -sS https://getcomposer.org/installer | php

# Ensures composer is installed and dependencies loaded
composer.lock: composer.phar
	./composer.phar --no-interaction install