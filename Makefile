DOCKER_COMPOSE = docker-compose

##
## Env Dev
##--------
install:
	touch docker/data/history
	$(DOCKER_COMPOSE) up -d

.PHONY : clean
