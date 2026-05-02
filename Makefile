.PHONY: up down setup

# Просто запустить проект (когда он уже настроен)
up:
	docker compose up -d

# Просто остановить проект
down:
	docker compose down

# ==============================================================================
#  ГЛАВНАЯ КОМАНДА: ПОЛНАЯ АВТОМАТИЧЕСКАЯ УСТАНОВКА ПРОЕКТА С НУЛЯ
# ==============================================================================
setup:
	@echo "1. Настройка .env файла..."
	@if [ ! -f ./src/.env ]; then cp ./src/.env.example ./src/.env; echo "✅ .env создан"; fi
	
	@echo "--> Настройка БД и Reverb..."
	@sed -i 's|^#*DB_CONNECTION=.*|DB_CONNECTION=pgsql|' ./src/.env
	@sed -i 's|^#*DB_HOST=.*|DB_HOST=db|' ./src/.env
	@sed -i 's|^#*DB_PORT=.*|DB_PORT=5432|' ./src/.env
	@sed -i 's|^#*DB_DATABASE=.*|DB_DATABASE=shop_db|' ./src/.env
	@sed -i 's|^#*DB_USERNAME=.*|DB_USERNAME=shop_user|' ./src/.env
	@sed -i 's|^#*DB_PASSWORD=.*|DB_PASSWORD=secret|' ./src/.env
	@sed -i 's|^#*BROADCAST_CONNECTION=.*|BROADCAST_CONNECTION=reverb|' ./src/.env
	@sed -i 's|^#*REDIS_HOST=.*|REDIS_HOST=redis|' ./src/.env

	@echo "--> Добавление ключей Reverb (если их нет)..."
	@grep -q "REVERB_APP_ID" ./src/.env || ( \
		echo "\n# Reverb Настройки" >> ./src/.env && \
		echo "REVERB_APP_ID=12345" >> ./src/.env && \
		echo "REVERB_APP_KEY=local_key" >> ./src/.env && \
		echo "REVERB_APP_SECRET=local_secret" >> ./src/.env && \
		echo "REVERB_HOST=localhost" >> ./src/.env && \
		echo "REVERB_PORT=6001" >> ./src/.env && \
		echo "REVERB_SCHEME=http" >> ./src/.env && \
		echo "VITE_REVERB_APP_KEY=\$${REVERB_APP_KEY}" >> ./src/.env && \
		echo "VITE_REVERB_HOST=\$${REVERB_HOST}" >> ./src/.env && \
		echo "VITE_REVERB_PORT=\$${REVERB_PORT}" >> ./src/.env \
	)

	@echo "2. Запуск контейнеров..."
	docker compose up -d

	@echo "3. Установка зависимостей и ключей..."
	docker compose exec app composer install --no-interaction
	docker compose exec app php artisan key:generate
	
	@echo "4. База данных (миграции и сиды)..."
	@sleep 3
	docker compose exec app php artisan migrate:fresh --seed --force
	docker compose exec app php artisan storage:link

	@echo "5. Сборка фронтенда..."
	docker compose run --rm node sh -c "npm install && npm run build"

	@echo "6. Права доступа..."
	docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
	docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
	@echo "✅ Все готово! http://localhost:8080"