.PHONY: up down restart rebuild setup setup-fresh prod-build prod-up prod-down prod-setup prod-deploy prod-shell prod-logs shell logs cache-clear test help

# === РАЗРАБОТКА ===
up:
	@docker compose up -d

down:
	@docker compose down

restart:
	@docker compose restart

rebuild:
	@docker compose build --no-cache

setup:
	@./scripts/setup.sh

setup-fresh:
	@echo "⚠️  Полная переустановка с очисткой БД..."
	@docker compose down -v
	@./scripts/setup.sh

# === ПРОДАКШЕН ===
prod-deploy:
	@chmod +x ./scripts/deploy.sh
	@./scripts/deploy.sh

prod-build:
	@docker compose -f docker-compose.prod.yml build --no-cache

prod-up:
	@docker compose -f docker-compose.prod.yml up -d

prod-down:
	@docker compose -f docker-compose.prod.yml down

prod-setup:
	@docker compose -f docker-compose.prod.yml exec app composer dump-autoload --optimize
	@docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
	@docker compose -f docker-compose.prod.yml exec app php artisan package:discover --ansi
	@docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
	@docker compose -f docker-compose.prod.yml exec app php artisan storage:link --force
	@docker compose -f docker-compose.prod.yml exec app php artisan config:cache
	@docker compose -f docker-compose.prod.yml exec app php artisan route:cache
	@docker compose -f docker-compose.prod.yml exec app php artisan view:cache

prod-seed:
	@docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force

prod-migrate:
	@docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

prod-shell:
	@docker compose -f docker-compose.prod.yml exec app bash

prod-logs:
	@docker compose -f docker-compose.prod.yml logs -f app

prod-logs-all:
	@docker compose -f docker-compose.prod.yml logs -f

prod-restart:
	@docker compose -f docker-compose.prod.yml restart

prod-ps:
	@docker compose -f docker-compose.prod.yml ps

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
	@echo ""
	@echo "📦 Laravel Docker — команды:"
	@echo ""
	@echo "  Продакшен:"
	@echo "  make prod-deploy   ← полный деплой одной командой ⭐"
	@echo "  make prod-build    ← собрать образы"
	@echo "  make prod-up       ← поднять контейнеры"
	@echo "  make prod-down     ← остановить контейнеры"
	@echo "  make prod-setup    ← миграции, кэши, storage:link"
	@echo "  make prod-seed     ← залить тестовые данные"
	@echo "  make prod-migrate  ← только миграции"
	@echo "  make prod-logs     ← логи Laravel"
	@echo "  make prod-logs-all ← логи всех контейнеров"
	@echo "  make prod-shell    ← зайти в контейнер"
	@echo "  make prod-restart  ← перезапустить контейнеры"
	@echo "  make prod-ps       ← статус контейнеров"
	@echo ""
	@echo "  Разработка:"
	@echo "  make setup         ← первый запуск"
	@echo "  make setup-fresh   ← полный сброс + настройка"
	@echo "  make up            ← поднять"
	@echo "  make down          ← остановить"
	@echo "  make shell         ← зайти в контейнер"
	@echo "  make logs          ← логи Laravel"
	@echo "  make cache-clear   ← очистить кэш"
	@echo "  make test          ← запустить тесты"
	@echo ""