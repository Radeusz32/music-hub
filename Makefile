SHELL := /bin/bash
SAIL := ./vendor/bin/sail

.PHONY: up down build restart npm-install dev start fresh clear rebuild

up:
	$(SAIL) up -d

down:
	$(SAIL) down

build:
	$(SAIL) build --no-cache

restart: down up

npm-install:
	$(SAIL) npm install

dev:
	$(SAIL) npm run dev

fresh:
	$(SAIL) artisan migrate:fresh --seed

start: up npm-install dev

# 🔥 Pełne czyszczenie środowiska
clear:
	$(SAIL) down -v --remove-orphans
	docker volume prune -f
	docker network prune -f
	docker container prune -f
	$(SAIL) up -d --build
	$(SAIL) artisan optimize:clear

# 🔥 Hard rebuild bez czyszczenia sieci/systemu
rebuild:
	$(SAIL) down -v
	docker volume prune -f
	$(SAIL) up -d --build
	$(SAIL) artisan optimize:clear
