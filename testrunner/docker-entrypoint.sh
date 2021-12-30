#!/usr/bin/env bash
echo Running tests


/root/.symfony/bin/symfony php bin/phpunit --coverage-text

echo This container will keep running for interactive tests
echo For example:
echo docker exec mmtest /root/.symfony/bin/symfony php bin/phpunit tests/Controller/AccountControllerTest

# just keep running
tail -f /dev/null
