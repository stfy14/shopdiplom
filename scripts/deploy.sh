#!/bin/bash
set -e

echo "🚀 Начинаем деплой..."

# --- 1. Проверяем .env ---
if [ ! -f ./src/.env ]; then
    echo "❌ Нет src/.env — положи его через WinSCP и запусти снова"
    exit 1
fi

# --- 2. Копируем .env в корень для docker-compose.prod.yml ---
echo "⚙️  Копируем .env в корень..."
cp ./src/.env ./.env

# --- 3. Поднимаем dev-контейнеры чтобы установить vendor ---
echo "📦 Устанавливаем composer зависимости..."
docker compose up -d app db redis
sleep 5
docker compose exec -T app composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# --- 4. Собираем фронтенд (vendor уже есть — ziggy найдётся) ---
echo "🎨 Собираем фронтенд..."
docker compose run --rm -T node sh -c "npm install && npm run build"

# --- 5. Останавливаем dev-контейнеры ---
echo "🛑 Останавливаем dev-контейнеры..."
docker compose down

# --- 6. Собираем прод-образы ---
echo "🐳 Собираем прод-образы..."
docker compose -f docker-compose.prod.yml build --no-cache

# --- 7. Поднимаем прод ---
echo "🐳 Поднимаем прод-контейнеры..."
docker compose -f docker-compose.prod.yml up -d

# --- 8. Ждём БД ---
echo "⏳ Ждём PostgreSQL..."
for i in {1..30}; do
    if docker compose -f docker-compose.prod.yml exec -T db pg_isready -U "${DB_USERNAME:-shop_user}" -d "${DB_DATABASE:-shop_db}" >/dev/null 2>&1; then
        echo "✅ PostgreSQL готова"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "❌ Таймаут: БД не запустилась"
        docker compose -f docker-compose.prod.yml logs db
        exit 1
    fi
    sleep 1
done

# --- 9. Артизан команды ---
echo "⚙️  Настраиваем Laravel..."
docker compose -f docker-compose.prod.yml exec -T app php artisan package:discover --ansi
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link --force
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# --- 10. Спрашиваем про сиды ---
echo ""
read -p "🌱 Залить тестовые данные (db:seed)? (y/N): " -n 1 -r < /dev/tty || true
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force
fi

# --- Готово ---
echo ""
echo "🎉 Деплой завершён!"
echo "🌐 Открывай: $(grep APP_URL ./src/.env | cut -d'=' -f2)"
echo ""
echo "💡 Полезные команды:"
echo "  make prod-logs    # логи"
echo "  make prod-down    # остановить"
echo "  make prod-shell   # зайти в контейнер"