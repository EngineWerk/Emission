#!/usr/bin/env bash

rm -rf ./app/cache/prod
cd ./src/Enginewerk/EmissionBundle/Resources/ && yarn install && cd -
composer install --no-ansi --no-dev --no-progress --no-scripts
php app/console c:c --env=prod
php app/console assetic:dump --env=prod
php app/console doctrine:migrations:migrate --env=prod
composer dump-autoload --optimize;
