#!/bin/sh

echo "=> Install composer"
curl -sS https://getcomposer.org/installer | php
mv /home/vagrant/composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    chown root:vagrant /usr/local/bin/composer

echo "=> Configuring Emission"
mkdir -p /vagrant/shared-storage/data && chmod a+rwX /vagrant/shared-storage/data

echo "=> Granting logs and cache permission"
chmod -R 777 /var/www/emission/app/logs /var/www/emission/app/cache
