#!/bin/sh
set -e

echo "Entry point: waiting for database..."

# Fix permissions for storage and bootstrap/cache before anything else
echo "Setting correct permissions on storage and bootstrap/cache"
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

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

# Ensure Node.js / pnpm available and build JS assets at container start.
# This allows the container to install dependencies and build assets if needed.
echo "Ensuring Node.js and pnpm are available"
if ! command -v node >/dev/null 2>&1; then
  echo "Node not found — installing Node.js (non-interactive)"
  apt-get update -y && apt-get install -y curl ca-certificates gnupg lsb-release || true
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - || true
    apt-get install -y nodejs || true
  fi
fi

if command -v corepack >/dev/null 2>&1; then
  corepack enable || true
  corepack prepare pnpm@7 --activate || true
elif command -v npm >/dev/null 2>&1; then
  npm i -g pnpm@7 --no-audit --no-fund || true
fi

if command -v pnpm >/dev/null 2>&1; then
  # Only run heavy install/build when necessary to avoid repeating on container restarts.
  if [ ! -f /var/www/public/build/manifest.json ]; then
    echo "Installing JS dependencies with pnpm (first-time or missing build)"
    pnpm install --unsafe-perm || echo "pnpm install failed"

    echo "Building assets with pnpm build"
    pnpm build || echo "pnpm build failed"
  else
    echo "public/build/manifest.json exists — skipping pnpm install/build"
  fi
else
  echo "pnpm not available — skipping JS install/build"
fi

# Always ensure storage symlink exists (user requested this run always)
echo "Creating storage symlink (always)"
# Temporarily disable errexit so an expected non-zero exit (link exists) doesn't stop the whole script
set +e
php artisan storage:link
PHP_STORAGE_LINK_RC=$?
if [ "$PHP_STORAGE_LINK_RC" -ne 0 ]; then
  echo "php artisan storage:link returned $PHP_STORAGE_LINK_RC — continuing"
fi
set -e

# Generate APP_KEY only if not set or empty in .env (always)
echo "Checking application key"
if [ -f /var/www/.env ]; then
  APP_KEY_VAL=$(grep -E '^APP_KEY=' /var/www/.env | cut -d= -f2-)
else
  APP_KEY_VAL=""
fi
if [ -z "$APP_KEY_VAL" ]; then
  echo "APP_KEY empty — generating"
  php artisan key:generate --force || echo "Key generation failed"
else
  echo "APP_KEY already present, skipping generation"
fi

# Check DB tables and run migrations/seeders if no tables exist (always)
echo "Checking if database has any tables"
# Temporarily disable errexit so we can inspect php's exit code and handle it
set +e
php -r 'try { $h=getenv("DB_HOST")?:"db"; $p=getenv("DB_PORT")?:3306; $d=getenv("DB_DATABASE"); $u=getenv("DB_USERNAME"); $pw=getenv("DB_PASSWORD"); $pdo=new PDO("mysql:host=$h;port=$p;dbname=$d", $u, $pw); $res=$pdo->query("SHOW TABLES"); $row=$res->fetch(PDO::FETCH_NUM); if ($row) exit(0); else exit(1); } catch (Exception $e) { exit(2); }'
RC=$?
set -e
if [ "$RC" -eq 1 ]; then
  echo "No tables found — running migrations and seeders"
  php artisan migrate --force || echo "Migrations failed"
  php artisan db:seed --force || echo "Seeders failed"
elif [ "$RC" -eq 0 ]; then
  echo "Database already has tables — skipping migrations"
else
  echo "Could not determine DB tables (rc=$RC) — skipping automatic migrate"
fi

if [ ! -f /var/www/storage/.initialized ]; then
  echo "First start detected: performing post-first-run optimizations"
  # Cache configuration and routes for better performance
  echo "Optimizing application"
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true

  touch /var/www/storage/.initialized
else
  echo "Already initialized — clearing and recaching config to pick up environment changes"
  php artisan config:clear || true
  php artisan config:cache || true
  php artisan route:clear || true
  php artisan view:clear || true
fi

echo "Entrypoint: executing CMD: $*"
exec "$@"
