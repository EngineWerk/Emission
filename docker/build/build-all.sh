#!/bin/bash
set -e

echo "Building Nginx"
./build.sh nginx 1.11-1
echo "Building php cli"
./build.sh php-cli 7.1-1
echo "Building php fpm"
./build.sh php-fpm 7.1-1
echo "Building mysql"
./build.sh mysql 5.5-1
