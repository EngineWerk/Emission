#!/usr/bin/env bash

sudo rm -rf ./app/cache/dev
composer install
php app/console c:c --env=dev
