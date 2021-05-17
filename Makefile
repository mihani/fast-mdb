DC = docker-compose

##
## Env Dev
##--------
install:
	touch docker/data/history
	cp .env .env.local
	$(DC) up -d
	$(DC) exec php composer install
	$(DC) exec php sf doctrine:migrations:migrate
	$(DC) exec node npm install
	$(DC) exec node yarn encore dev

.PHONY : clean

##
## Quality assurance
## -----------------
phpcs-fixer:
	$(DC) exec php vendor/bin/php-cs-fixer fix --verbose

.PHONY : clean
