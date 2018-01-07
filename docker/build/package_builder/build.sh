#!/usr/bin/env bash

set -e

HOME_DIR=$(pwd)
BUILD_DIRECTORY=/opt/build
REPOSITORY_DIRECTORY=/opt/repository
APP_SOURCE_ROOT=${REPOSITORY_DIRECTORY}/emission

echo "Repository directory"
ls -lash ${REPOSITORY_DIRECTORY}

cd ${REPOSITORY_DIRECTORY}
COMMIT_HASH="$(git rev-parse --short --verify HEAD)"
cd ${HOME_DIR}

composer install \
    --no-ansi \
    --no-dev \
    --no-progress \
    --working-dir="${APP_SOURCE_ROOT}"

php ${APP_SOURCE_ROOT}/app/console c:c --env=prod
php ${APP_SOURCE_ROOT}/app/console assetic:dump --env=prod

composer dump-autoload --optimize --working-dir="${APP_SOURCE_ROOT}"

PHP_PACKAGE_NAME=php-app-package-${COMMIT_HASH}.tar.gz

echo "Build directory"
ls -lash ${BUILD_DIRECTORY}
tar -cjf ${BUILD_DIRECTORY}/${PHP_PACKAGE_NAME} -C ${APP_SOURCE_ROOT} . \
--exclude-vcs \
--exclude=app/logs \
--exclude=web/app_dev.php \
--exclude=parameters.yml \
--exclude=kernel.ini \
--exclude=web/bundles \
--exclude=web/js

WEB_PACKAGE_NAME=web-app-package-${COMMIT_HASH}.tar.gz

tar -hcjf ${BUILD_DIRECTORY}/${WEB_PACKAGE_NAME} -C ${APP_SOURCE_ROOT}/web . \
--exclude=*.php

ls -lash ${BUILD_DIRECTORY}
