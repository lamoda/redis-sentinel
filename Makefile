
.PHONY: default
default: test

test:
	@./vendor/bin/phpunit

php-cs-fix:
	@./vendor/bin/php-cs-fixer fix -vvv

up:
	docker-compose -f docker/docker-compose.yml up -d

down:
	docker-compose -f docker/docker-compose.yml down -v
