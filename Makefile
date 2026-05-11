# Makefile
.PHONY: up down restart rebuild setup setup-fresh prod-build prod-up prod-down shell logs cache-clear test help

# === РАЗРАБОТКА ===
up:
	@docker compose up -d

down:
	@docker compose down

restart:
	@docker compose restart

rebuild:
	@docker compose build --no-cache

# Первый запуск (с вопросом про очистку БД)
setup:
	@./scripts/setup.sh

# Принудительный сброс + настройка (без вопроса)
setup-fresh:
	@echo "⚠️  Полная переустановка с очисткой БД..."
	@docker compose down -v
	@./scripts/setup.sh

# === ПРОДАКШЕН ===
prod-build:
	@docker compose -f docker-compose.prod.yml build --no-cache

prod-up:
	@docker compose -f docker-compose.prod.yml up -d

prod-down:
	@docker compose -f docker-compose.prod.yml down

prod-migrate:
	@docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# === УТИЛИТЫ ===
shell:
	@docker compose exec app bash

logs:
	@docker compose logs -f app

logs-all:
	@docker compose logs -f

cache-clear:
	@docker compose exec app php artisan optimize:clear

test:
	@docker compose exec app php artisan test

help:
	@echo "📦 Laravel Docker — команды:"
	@echo ""
	@echo "Разработка:"
	@echo "  make setup        ← первый запуск (спросит про БД)"
	@echo "  make setup-fresh  ← полный сброс + настройка"
	@echo "  make up/down      ← поднять/остановить"
	@echo "  make shell        ← войти в контейнер"
	@echo "  make logs         ← логи Laravel"
	@echo ""
	@echo "Продакшен:"
	@echo "  make prod-build   ← собрать чистые образы"
	@echo "  make prod-up      ← запустить прод"
	@echo "  make prod-migrate ← применить миграции"
	@echo ""
	@echo "Утилиты:"
	@echo "  make cache-clear  ← очистить кэш Laravel"
	@echo "  make test         ← запустить тесты"