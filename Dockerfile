# Dockerfile para Laravel (PHP + Composer + Node + pnpm)

FROM php:8.2-fpm AS php_base

# Instala dependencias del sistema
RUN apt-get update \
    && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        git \
        curl \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instala Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www


FROM node:20-bookworm-slim AS node_builder

WORKDIR /app

# Archivos de build (Vite/Tailwind)
COPY package.json pnpm-lock.yaml vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public

RUN corepack enable \
    && corepack prepare pnpm@7 --activate \
    && pnpm config set auto-install-peers true \
    && pnpm install --frozen-lockfile \
    && pnpm build


FROM php_base AS app

# Copia el código PHP (sin node_modules/vendor por .dockerignore)
COPY . /var/www

# Copy entrypoint and make executable
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
# Install Node.js (needed at runtime to run pnpm install/build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && corepack enable

# Dependencias PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copia los assets compilados (evita incluir el store de pnpm en la imagen final)
COPY --from=node_builder /app/public/build /var/www/public/build

# Permisos
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
