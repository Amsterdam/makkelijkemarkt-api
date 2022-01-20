APP_ENV=test /root/.symfony/bin/symfony console doctrine:migrations:migrate -n
APP_ENV=test /root/.symfony/bin/symfony console doctrine:fixtures:load -n --purge-with-truncate
