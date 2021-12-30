curl -sS https://get.symfony.com/cli/installer | bash
APP_ENV=test /root/.symfony/bin/symfony console doctrine:database:drop --force || true
APP_ENV=test /root/.symfony/bin/symfony console doctrine:database:create
