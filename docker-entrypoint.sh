#!/bin/bash
set -e
chmod -R 777 /var/www/html/logs 2>/dev/null || true
exec apache2-foreground
