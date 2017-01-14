#!/usr/bin/env bash

sudo rm -rf ./app/cache/prod
composer install --no-ansi --no-dev --no-progress --no-scripts;
php app/console c:c --env=prod
composer dump-autoload --optimize;
