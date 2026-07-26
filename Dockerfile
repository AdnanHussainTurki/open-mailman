# Stage 1: frontend build
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --no-fund --ignore-scripts
COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public/ ./public/
RUN npm run build

# Stage 2: runtime
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor zip unzip git curl mariadb-client shadow \
    libzip-dev $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip opcache \
    && apk del $PHPIZE_DEPS

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist --no-dev --optimize-autoloader
COPY . .
# Stale build-machine caches reference dev-only packages -> "Class not found" at runtime
RUN rm -f bootstrap/cache/*.php

COPY --from=node-builder /app/public/build ./public/build

# SGID on storage/logs + umask 0002 keeps log files group-writable regardless of
# whether root (cron) or www-data (fpm) creates them first
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/log/cron /var/lib/nginx /run/nginx \
      /tmp/nginx/client_body storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache /var/log/nginx \
      /var/log/supervisor /var/lib/nginx /run/nginx /tmp/nginx \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 777 /tmp/nginx \
    && chown root:www-data storage/logs && chmod 2775 storage/logs

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/fallback.html /usr/local/share/fallback.html
COPY docker/pre-nginx.conf /usr/local/share/pre-nginx.conf
COPY docker/nginx.conf /usr/local/share/nginx.conf
COPY docker/crontab /etc/crontabs/root
RUN chmod 600 /etc/crontabs/root \
    && sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf 2>/dev/null || true \
    && chmod +x /usr/local/bin/entrypoint.sh

RUN rm -rf node_modules tests .git .github docker /tmp/* /var/cache/apk/*

EXPOSE 80
HEALTHCHECK --interval=15s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -f http://127.0.0.1:80/api/health/live || exit 1
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
