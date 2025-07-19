.DEFAULT_GOAL := help

PHP = php
CONSOLE = $(PHP) bin/console
Command := $(firstword $(MAKECMDGOALS))
Arguments := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
PG_USER = ms
PG_DBNAME = ms

# Установка проекта с docker
docker-install:
	@if [ -z "$$(docker network ls | grep caddy)" ]; then \
		echo "Сеть caddy не найдена. Создание сети..."; \
		docker network create --driver bridge caddy; \
	else \
		echo "Сеть caddy уже существует."; \
	fi;
	@docker compose -f docker-compose.yml --env-file .env.local up --build -d --remove-orphans --force-recreate

# Установка проекта с docker для прода
docker-install-prod:
	@if [ -z "$$(docker network ls | grep caddy)" ]; then \
		echo "Сеть caddy не найдена. Создание сети..."; \
		docker network create --driver bridge caddy; \
	else \
		echo "Сеть caddy уже существует."; \
	fi;
	@docker compose -f docker-compose-prod.yml --env-file .env.local up --build -d --remove-orphans --force-recreate

# Запуск команд внутри php контейнера
dphp:
	@docker exec -it php-container $(cmd)

# Запуск тестов
test:
	$(CONSOLE) --env=test doctrine:database:create --if-not-exists -vv
	$(CONSOLE) --env=test doctrine:schema:drop --force -vv
	$(CONSOLE) --env=test doctrine:schema:create -vv
	$(PHP) bin/phpunit --testdox --colors

# Очистка кэша Symfony
cache-clear:
	$(CONSOLE) cache:clear

# Применение миграций
migrate:
	$(CONSOLE) doctrine:migrations:migrate
	$(CONSOLE) cache:clear

# Применение миграций
docker-migrate:
	@docker exec -it php-container bin/console doctrine:migrations:migrate
	@docker exec -it php-container bin/console cache:clear

# Создание новой миграции
migration:
	$(CONSOLE) make:migration

# Помощь
help:
	@echo "Доступные команды:\n" \
    "docker-install           Установка проекта в docker для разработки\n" \
    "docker-install-prod      Установка проекта в docker для релиза\n" \
    "dphp                     Запуск команд внутри php контейнера\n" \
    "test                     Запуск тестов\n" \
    "cache-clear              Очистка кэша Symfony\n" \
    "migrate                  Применение миграций\n" \
    "docker-migrate           Применение миграций внутри docker контейнера\n" \
    "migration                Создание новой миграции\n" \
    "help                     Помощь\n" \
	"openapi                  Генерация документации Swagger для API\n"

# Документация
openapi:
	touch ./doc/openapi.yaml && $(CONSOLE) nelmio:apidoc:dump --format=yaml > ./doc/openapi.yaml
