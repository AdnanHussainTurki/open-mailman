#!/bin/sh
set -e
umask 0002
export HOME=/var/www/html

# 1. Serve static maintenance page immediately
cp /usr/local/share/fallback.html /var/www/html/index.html
mkdir -p /etc/nginx/http.d
cp /usr/local/share/pre-nginx.conf /etc/nginx/http.d/default.conf
if ! grep -q "^user " /etc/nginx/nginx.conf; then sed -i '1i user www-data;' /etc/nginx/nginx.conf; fi
nginx -g "daemon off;" & NGINX_PID=$!

# 2. Prepare the app while maintenance page is up
cd /var/www/html
rm -f /var/www/html/bootstrap/cache/*.php
php artisan config:clear && php artisan route:clear && php artisan view:clear
if [ "$APP_ENV" != "production" ] && [ "$APP_ENV" != "prod" ]; then
    composer install --no-interaction --optimize-autoloader
fi
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link || true

# 3. Log files: fresh ones owned by www-data
chown root:www-data /var/www/html/storage/logs && chmod 2775 /var/www/html/storage/logs
for f in laravel.log schedule.log cron.log; do
    touch /var/www/html/storage/logs/$f
done
find /var/www/html/storage/logs -maxdepth 1 -type f -exec chown www-data:www-data {} + -exec chmod 664 {} +

# 4. Swap maintenance nginx for the real config, hand off to supervisord
rm -f /var/www/html/index.html
kill $NGINX_PID
cp /usr/local/share/nginx.conf /etc/nginx/http.d/default.conf
mkdir -p /tmp/nginx/client_body /run/nginx /var/log/cron
chown -R www-data:www-data /tmp/nginx /run/nginx /var/log/nginx
rm -f /run/nginx/nginx.pid
nginx -t
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
