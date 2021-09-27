#!/usr/bin/env bash
echo Starting server

set -u
set -e

cd /app
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:status
php bin/console --no-interaction doctrine:migrations:migrate
php bin/console cache:clear --env=prod
chown -R www-data:www-data /app/var/cache && find /app/var/cache -type d -exec chmod -R 0770 {} \; && find /app/var/cache -type f -exec chmod -R 0660 {} \;
#php bin/console assetic:dump --env=prod

# Configure access to /download URL
mkdir -p /etc/nginx/htpasswd.d
echo -e $MM_API__NGINX_HTPASSWD > /etc/nginx/htpasswd.d/makkelijkemarkt-api.amsterdam.nl

# Make sure log files exist, so tail won't return a non-zero exitcode
touch /app/var/log/dev.log
touch /app/var/log/prod.log
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log

chgrp www-data /app/var/log/*.log
chmod 775 /app/var/log/*.log

tail -f /app/var/log/dev.log &
tail -f /app/var/log/prod.log &
tail -f /var/log/nginx/access.log &
tail -f /var/log/nginx/error.log &

nginx

chgrp -R www-data /var/lib/nginx
chmod -R 775 /var/lib/nginx/tmp

php-fpm -F
