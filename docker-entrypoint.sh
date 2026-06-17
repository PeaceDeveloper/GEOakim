#!/bin/bash
set -e
chmod -R 777 /var/www/app/logs 2>/dev/null || true
if [ -f /var/www/app/composer.json ] && [ ! -d /var/www/app/vendor ]; then
  composer install --no-dev --optimize-autoloader --no-interaction || true
fi
exec apache2-foreground
