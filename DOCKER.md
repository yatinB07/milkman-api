# MilkMan API Docker

This Docker setup runs only the Laravel API. MySQL is not included.

```bash
docker compose up --build
```

URL:

- API: http://localhost:8000
- Health: http://localhost:8000/api/v1/health

Configuration:

- Laravel reads `milkman-api/.env` from the mounted project directory.
- Database credentials stay only in `.env`; they are not duplicated or imported in `docker-compose.yml`.
- The compose file uses host networking for local development, so `DB_HOST=127.0.0.1` points to your host MySQL.
- If the database is on another server, update `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

Startup runs:

```bash
composer install --no-interaction --prefer-dist
php artisan config:clear
php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=8000
```

Seeders are not run automatically. Run them manually only when needed:

```bash
docker compose exec api php artisan db:seed --class=DemoDataSeeder --force
```

Useful commands:

```bash
docker compose ps
docker compose logs -f api
docker compose exec api php artisan test
docker compose exec api php artisan migrate:status
docker compose down
```
