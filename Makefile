up:
	docker compose up -d

setup:
	docker compose up -d
	sleep 5
	docker compose exec app php artisan migrate --seed
	docker compose exec app php artisan storage:link