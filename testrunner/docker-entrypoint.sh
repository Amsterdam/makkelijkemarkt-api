#!/usr/bin/env bash
echo Running tests

set -u
set -e

/root/.symfony/bin/symfony php bin/phpunit --coverage-text
