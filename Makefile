tests: phpcs phpstan phpunit

phpcs:
	./vendor/bin/phpcs --standard=PSR2 src

phpstan:
	./vendor/bin/phpstan analyse -l max src

phpunit:
	./vendor/bin/phpunit
