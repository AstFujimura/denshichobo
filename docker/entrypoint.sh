#!/bin/bash
set -e
cd /var/www/html

mkdir -p \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache/data \
  storage/logs \
  storage/app/public \
  bootstrap/cache

# ホストの .env がある場合は書き換えない。無いときだけ開発用に作る。
if [ ! -f .env ] && [ "${APP_ENV}" != "production" ]; then
  cp .env.example .env
fi

# 開発用の名前付きボリュームは空で始まる。本番イメージには vendor が入っている。
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist --no-scripts
  composer dump-autoload --optimize --no-interaction --no-scripts
fi

if [ ! -f bootstrap/cache/packages.php ]; then
  php artisan package:discover --ansi
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

db_ready() {
  php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: "3306"), getenv("DB_USERNAME") ?: "root", getenv("DB_PASSWORD") !== false ? getenv("DB_PASSWORD") : ""); exit(0); } catch (Throwable $e) { exit(1); }'
}

if [ "${RUN_CONTROL_MIGRATIONS:-false}" = "true" ]; then
  echo "Waiting for database..."
  ready=0
  for _ in $(seq 1 30); do
    if db_ready; then
      ready=1
      break
    fi
    sleep 2
  done
  if [ "$ready" != "1" ]; then
    echo "Database is not reachable." >&2
    exit 1
  fi
  php artisan control:migrate --force
fi

if [ "${APP_ENV}" = "production" ]; then
  php artisan config:cache
  php artisan view:cache
else
  php artisan config:clear
fi

exec apache2-foreground
