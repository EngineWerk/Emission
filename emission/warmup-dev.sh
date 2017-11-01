#!/usr/bin/env bash

rm -rf ./app/cache/dev
cd ./src/Enginewerk/EmissionBundle/Resources/ && yarn install && cd -
composer install
php app/console security:check --env=dev
php app/console doctrine:migrations:migrate --env=dev
php app/console c:c --env=dev
