
.PHONY: default
default: test

test:
	@./vendor/bin/phpunit

php-cs-fix:
	@./vendor/bin/php-cs-fixer fix -vvv

up:
	cd docker && docker-compose up -d

down:
	cd docker && docker-compose down -v
