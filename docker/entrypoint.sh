#!/bin/sh
set -e

echo "Entry point: waiting for database..."

DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}

MAX_WAIT=60
WAITED=0

until php -r ' $s=@fsockopen(getenv("DB_HOST")?:"db", getenv("DB_PORT")?:3306, $e, $m, 1); if ($s) { fclose($s); exit(0); } exit(1); ' >/dev/null 2>&1; do
  WAITED=$((WAITED+1))
  if [ "$WAITED" -ge "$MAX_WAIT" ]; then
    echo "Timed out waiting for database at $DB_HOST:$DB_PORT" >&2
    break
  fi
  echo "Waiting for DB ($DB_HOST:$DB_PORT)... ($WAITED)"
  sleep 1
done

if [ ! -f /var/www/.initialized ]; then
  echo "First start detected: preparing frontend + running migrations"
  # Install JS deps first time
  if [ ! -f /var/www/.pnpm_installed ]; then
    echo "Running pnpm install (first start)"
    cd /var/www || exit 1
    corepack prepare pnpm@7 --activate || corepack enable || true
    pnpm install --frozen-lockfile || pnpm install || true
    touch /var/www/.pnpm_installed
  fi

  # Always build assets on start
  echo "Running pnpm build"
  cd /var/www || exit 1
  corepack enable || true
  pnpm run build || echo "pnpm build failed"

  echo "Running migrations"
  php artisan config:clear || true
  php artisan migrate --force || echo "Migrations failed"
  touch /var/www/.initialized
else
  echo "Already initialized, skipping migrations"
  # Ensure npm deps exist and always build assets on each start
  if [ ! -f /var/www/.pnpm_installed ]; then
    echo "pnpm not installed yet, running pnpm install"
    cd /var/www || exit 1
    corepack prepare pnpm@7 --activate || corepack enable || true
    pnpm install --frozen-lockfile || pnpm install || true
    touch /var/www/.pnpm_installed
  fi

  echo "Running pnpm build"
  cd /var/www || exit 1
  corepack enable || true
  pnpm run build || echo "pnpm build failed"
fi

echo "Entrypoint: executing CMD"
exec "$@"
