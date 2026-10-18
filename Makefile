DC = docker compose
APP = $(DC) exec app

.DEFAULT_GOAL := help

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

setup: ## First-time setup on a new machine
	@test -f .env || cp .env.example .env
	@sed -i "s/^UID=.*/UID=$$(id -u)/; s/^GID=.*/GID=$$(id -g)/" .env
	$(DC) build
	$(DC) run --rm --no-deps app composer install
	@grep -q '^APP_KEY=base64' .env || $(DC) run --rm --no-deps app php artisan key:generate
	$(DC) up -d
	$(APP) php artisan migrate --force
	$(APP) php artisan storage:link
	@echo "\nApplication ready: http://localhost:$$(grep ^APP_PORT .env | cut -d= -f2)"

up: ## Start the containers
	$(DC) up -d

down: ## Stop the containers
	$(DC) down

restart: down up ## Restart the containers

build: ## Rebuild the images
	$(DC) build --pull

logs: ## Follow the logs
	$(DC) logs -f

ps: ## List the containers
	$(DC) ps

shell: ## Open a shell in the PHP container
	$(APP) bash

artisan: ## Run an artisan command: make artisan c="migrate"
	$(APP) php artisan $(c)

composer: ## Run a composer command: make composer c="require x/y"
	$(APP) composer $(c)

npm: ## Run an npm command: make npm c="install x"
	$(DC) exec node npm $(c)

assets: ## Build front-end assets for production
	$(DC) run --rm --no-deps node npm run build

migrate: ## Run the migrations
	$(APP) php artisan migrate

fresh: ## Reset the database and run the seeders
	$(APP) php artisan migrate:fresh --seed

test: ## Run the test suite
	$(APP) php artisan test

tinker: ## Open Tinker
	$(APP) php artisan tinker

db: ## Open a PostgreSQL client
	$(DC) exec postgres sh -c 'psql -U "$$POSTGRES_USER" "$$POSTGRES_DB"'

clean: ## Remove containers AND volumes (data is lost!)
	$(DC) down -v

.PHONY: help setup up down restart build logs ps shell artisan composer npm assets migrate fresh test tinker db clean
