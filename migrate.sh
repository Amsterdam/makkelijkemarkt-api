#!/bin/sh

php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:status
php bin/console --no-interaction doctrine:migrations:migrate
