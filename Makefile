DOCKER_COMPOSE  = docker compose

EXEC_PHP        = $(DOCKER_COMPOSE) run --rm php

SYMFONY         = $(EXEC_PHP) bin/console
COMPOSER        = $(EXEC_PHP) composer

## Application

build:	## Build
	touch docker/php/conf/history
	$(DOCKER_COMPOSE) build

start:	## start
	$(DOCKER_COMPOSE) up -d --remove-orphans --force-recreate

stop:	## stop
	$(DOCKER_COMPOSE) down

vendor:	## vendor
	$(COMPOSER) install

restart: stop start	## Restart

kill:	## kill
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

php: ## Enter shell in php container
	$(EXEC_PHP) bash

cc: ## Clear the cache
	$(SYMFONY) cache:clear

cc-test: ## Clear the cache
	$(SYMFONY) cache:clear --env=test

diff: ## Create new migration
	$(SYMFONY) doctrine:migrations:diff

db:                                        ## Reset the database
	@$(EXEC_PHP) php docker/php/wait-database.php
	$(SYMFONY) doctrine:database:drop --if-exists --force
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration
	$(SYMFONY) doctrine:fixtures:load --no-interaction
	$(SYMFONY) doctrine:schema:validate

install: start vendor db	## Install

cs:	## Apply cs fixer
	$(EXEC_PHP) vendor/bin/php-cs-fixer fix --verbose --diff --show-progress=dots

psalm:	## Psalm
	$(EXEC_PHP) ./vendor/bin/psalm --no-cache

psalm-cc: ## Run Psalm cache clear
	rm -rf var/cache/psalm

dust-psalm: ## Clean Psalm baseline
	$(EXEC_PHP) ./vendor/bin/psalm --update-baseline

baseline-psalm: ## Update Psalm baseline
	$(EXEC_PHP) ./vendor/bin/psalm --set-baseline=psalm-baseline.xml

rector:
	$(EXEC_PHP) ./vendor/bin/rector process src tests

qa: rector cs psalm	## Lance la QA

## Testing

db-test:                                                    		## Init the test database and load fixtures
	$(SYMFONY) doctrine:database:drop --if-exists --force --env=test
	$(SYMFONY) doctrine:database:create --if-not-exists --env=test
	$(SYMFONY) doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test
	$(SYMFONY) doctrine:fixtures:load --no-interaction --env=test
	$(SYMFONY) doctrine:schema:validate --env=test

test: db-test                                                		## Run all the test suite
	$(EXEC_PHP) bin/phpunit

test-group: db-test	## Lance des tests par groupe
	$(EXEC_PHP) bin/phpunit --group=$$group

.PHONY: db-test test

## HELP
help:                                                      			## show the help
	@grep -E '(^[0-9a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.DEFAULT_GOAL := help
