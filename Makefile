DOCKER_COMPOSE = docker-compose

##
## Env Dev
##--------
install:
	touch docker/data/history
	cp .env .env.local
	$(DOCKER_COMPOSE) up -d

.PHONY : clean

##
## Quality assurance
## -----------------
phpcs-fixer:
	$(DOCKER_COMPOSE) run --rm php vendor/bin/php-cs-fixer fix --verbose

.PHONY : clean
