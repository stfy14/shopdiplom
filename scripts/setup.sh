#!/bin/bash
# scripts/setup.sh
set -e  # Выход при любой ошибке

echo "🔧 Настройка Laravel + Docker..."

# --- 1. Проверяем .env ---
if [ ! -f ./src/.env ]; then
    if [ -f ./src/.env.example ]; then
        cp ./src/.env.example ./src/.env
        echo "✅ .env создан из .env.example"
    else
        echo "❌ Ошибка: нет ни .env, ни .env.example"
        exit 1
    fi
fi

# --- 2. Универсальная функция sed (Linux + macOS) ---
replace_in_file() {
    local pattern=$1
    local file=$2
    if sed --version >/dev/null 2>&1; then
        # Linux
        sed -i "$pattern" "$file"
    else
        # macOS
        sed -i '' "$pattern" "$file"
    fi
}

# --- 3. Настраиваем переменные под Docker ---
echo "⚙️ Применяем настройки для Docker..."

# DB
replace_in_file 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' ./src/.env
replace_in_file 's|^DB_HOST=.*|DB_HOST=db|' ./src/.env
replace_in_file 's|^DB_PORT=.*|DB_PORT=5432|' ./src/.env
replace_in_file 's|^DB_DATABASE=.*|DB_DATABASE=shop_db|' ./src/.env
replace_in_file 's|^DB_USERNAME=.*|DB_USERNAME=shop_user|' ./src/.env
replace_in_file 's|^DB_PASSWORD=.*|DB_PASSWORD=secret|' ./src/.env

# Redis
replace_in_file 's|^REDIS_HOST=.*|REDIS_HOST=redis|' ./src/.env
replace_in_file 's|^REDIS_PORT=.*|REDIS_PORT=6379|' ./src/.env

# Cache/Session/Queue
replace_in_file 's|^SESSION_DRIVER=.*|SESSION_DRIVER=redis|' ./src/.env
replace_in_file 's|^CACHE_STORE=.*|CACHE_STORE=redis|' ./src/.env
replace_in_file 's|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|' ./src/.env

# Reverb
replace_in_file 's|^BROADCAST_CONNECTION=.*|BROADCAST_CONNECTION=reverb|' ./src/.env

# --- 4. Добавляем Reverb-переменные, если их нет ---
if ! grep -q "^REVERB_APP_ID=" ./src/.env; then
    echo "" >> ./src/.env
    echo "# Reverb" >> ./src/.env
    echo "REVERB_APP_ID=12345" >> ./src/.env
    echo "REVERB_APP_KEY=local_key" >> ./src/.env
    echo "REVERB_APP_SECRET=local_secret" >> ./src/.env
    echo "REVERB_HOST=reverb" >> ./src/.env
    echo "REVERB_PORT=6001" >> ./src/.env
    echo "REVERB_SCHEME=http" >> ./src/.env
    echo "VITE_REVERB_APP_KEY=\${REVERB_APP_KEY}" >> ./src/.env
    echo "VITE_REVERB_HOST=localhost" >> ./src/.env
    echo "VITE_REVERB_PORT=\${REVERB_PORT}" >> ./src/.env
    echo "VITE_REVERB_SCHEME=\${REVERB_SCHEME}" >> ./src/.env
    echo "✅ Reverb-настройки добавлены"
fi

# --- 5. Запускаем контейнеры ---
echo "🐳 Запускаем Docker-контейнеры..."
docker compose up -d

# --- 6. Ждём, пока БД станет доступна (макс. 30 сек) ---
echo "⏳ Ожидаем готовности базы данных..."
for i in {1..30}; do
    if docker compose exec -T db pg_isready -U shop_user -d shop_db >/dev/null 2>&1; then
        echo "✅ PostgreSQL готова"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "❌ Таймаут: база не запустилась"
        docker compose logs db
        exit 1
    fi
    sleep 1
done

# --- 7. Устанавливаем зависимости ---
echo "📦 Composer install..."
docker compose exec -T app composer install --no-interaction --no-progress

# --- 8. APP_KEY: генерируем только если пустой ---
CURRENT_KEY=$(grep "^APP_KEY=" ./src/.env | cut -d'=' -f2)
if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = "" ]; then
    echo "🔑 Генерируем APP_KEY..."
    docker compose exec -T app php artisan key:generate --force
else
    echo "✅ APP_KEY уже установлен, пропускаем"
fi

# --- 9. Миграции: спрашиваем перед destructive-операцией ---
echo ""
echo "⚠️  Внимание: migrate:fresh удалит ВСЕ данные в БД!"
read -p "Продолжить с очисткой БД? (y/N): " -n 1 -r < /dev/tty || true
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🗑️  Очищаем БД и применяем миграции + сиды..."
    docker compose exec -T app php artisan migrate:fresh --seed --force
else
    echo "🔄 Применяем только новые миграции (без очистки)..."
    docker compose exec -T app php artisan migrate --force
fi

# --- 10. Storage link ---
echo "🔗 Создаём symlink для storage..."
docker compose exec -T app php artisan storage:link --force 2>/dev/null || true

# --- 11. Сборка фронтенда ---
echo "🎨 Сборка фронтенда (Vite)..."
docker compose run --rm -T node sh -c "npm install && npm run build"

# --- 12. Права доступа ---
echo "🔐 Настраиваем права..."
docker compose exec -T app chown -R www-www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# --- Готово ---
echo ""
echo "🎉 Всё готово!"
echo "🌐 Открывай: http://localhost:8080"
echo ""
echo "💡 Полезные команды:"
echo "  make logs       # смотреть логи Laravel"
echo "  make shell      # зайти в контейнер"
echo "  make down       # остановить всё"