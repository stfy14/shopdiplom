up:
	docker compose up -d

down:
	docker compose down

setup:
	docker compose up -d
	sleep 5
	docker compose exec app php artisan migrate --seed
	docker compose exec app php artisan storage:link