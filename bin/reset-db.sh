#!/bin/bash

IS_ENV_DEV=$(bin/console debug:container --parameter=kernel.environment --format=json | grep -c '"kernel.environment": "dev"')
IS_ENV_TEST=$(bin/console debug:container --parameter=kernel.environment --format=json | grep -c '"kernel.environment": "test"')

if [[ "$IS_ENV_DEV" -le 0 && "$IS_ENV_TEST" -le 0 ]]; then
    echo 'This script can only be used when APP_ENV is set to "dev" or "test"'
    exit 1
fi

bin/console cache:clear
bin/console doctrine:schema:drop --full-database --force --no-interaction
bin/console doctrine:migrations:migrate --no-interaction
bin/console doctrine:schema:update --dump-sql --force --no-interaction
bin/console doctrine:fixtures:load --no-interaction