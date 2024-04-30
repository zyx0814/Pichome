install: composer.phar
	./composer.phar install

update: composer.phar
	./composer.phar self-update
	./composer.phar update

test: composer.lock
	./vendor/bin/phpunit

cs-check: composer.lock
	./vendor/bin/phpcs --standard=PSR1,PSR12 --encoding=UTF-8 --report=full --colors lib tests examples

coverage: composer.lock build
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover build/logs/clover.xml
	./vendor/bin/php-coveralls -v

composer.phar:
	curl -s http://getcomposer.org/installer | php

composer.lock: composer.phar
	./composer.phar --no-interaction install

vendor/bin/phpunit: install

build:
	mkdir build

clean:
	rm composer.phar
	rm -r vendor
	rm -r build
