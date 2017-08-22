#!/usr/bin/env bash
locale-gen en_US

apt-get update
apt-get -y install \
software-properties-common \
python-software-properties \
debian-keyring \
debian-archive-keyring \
apt-transport-https \
lsb-release \
ca-certificates

wget -O /tmp/dotdeb.gpg http://www.dotdeb.org/dotdeb.gpg \
&& apt-key add /tmp/dotdeb.gpg \
&& rm /tmp/dotdeb.gpg

echo "deb http://packages.dotdeb.org wheezy-php56 all" >> /etc/apt/sources.list.d/dotdeb.list
echo "deb-src http://packages.dotdeb.org wheezy-php56 all" >> /etc/apt/sources.list.d/dotdeb.list

apt-key update
apt-get update
apt-get upgrade

export DBPASSWORD=vagrant
echo mysql-server mysql-server/root_password password vagrant | debconf-set-selections
echo mysql-server mysql-server/root_password_again password vagrant | debconf-set-selections

apt-get install -y php5-cli

apt-get install -y nginx \
    php5-fpm \
    php5-mysql \
    php5-fpm \
    php5-curl \
    php5-intl \
    php5-dev \
    php5-xdebug \
    mysql-server \
    build-essential \
    curl \
    vim \
    mc \
    htop \
    git \
    strace

echo "xdebug.remote_host = 192.168.200.1" >> /etc/php5/conf.d/20-xdebug.ini
echo "xdebug.remote_enable = 1" >> /etc/php5/conf.d/20-xdebug.ini
echo "xdebug.remote_port = 9000" >> /etc/php5/conf.d/20-xdebug.ini
echo "xdebug.remote_handler = dbgp" >> /etc/php5/conf.d/20-xdebug.ini
echo "xdebug.remote_mode = req" >> /etc/php5/conf.d/20-xdebug.ini

mkdir -p /var/www

ln -s /emission /var/www/emission

echo "Setting up nginx vhosts"
cp /vagrant/config/emission/emission.local.conf /etc/nginx/sites-available/

ln -s /etc/nginx/sites-available/emission.local.conf /etc/nginx/sites-enabled/

/etc/init.d/nginx restart

echo "Creating emission database ..."
mysql -u root -p$DBPASSWORD -e 'CREATE DATABASE emission;'
mysql -u root -p$DBPASSWORD  -e "grant all privileges on *.* to 'root'@'%' identified by 'vagrant' with grant option;"
mysql -u root -p$DBPASSWORD  -e "flush privileges;"

echo "listen.owner = www-data" >> /etc/php5/fpm/pool.d/www.conf
echo "listen.group = www-data" >> /etc/php5/fpm/pool.d/www.conf
echo "listen.mode = 0660" >> /etc/php5/fpm/pool.d/www.conf

echo "=> Configuration for PHP"
sed -i "s/display_errors:.*/display_errors: On/g" /etc/php5/cli/php.ini
sed -i "s/display_errors:.*/display_errors: On/g" /etc/php5/fpm/php.ini
sed -i "s/;date.timezone =.*/date.timezone = UTC/g" /etc/php5/cli/php.ini
sed -i "s/upload_max_filesize =.*/upload_max_filesize = 100M/g" /etc/php5/fpm/php.ini
sed -i "s/post_max_size =.*/post_max_size = 101M/g" /etc/php5/fpm/php.ini
sed -i "s/file_uploads =.*/file_uploads = On/g" /etc/php5/fpm/php.ini

/etc/init.d/php5-fpm restart

#autoconfigure to allow phpstorm to debug connections properly
echo 'export XDEBUG_CONFIG="idekey=PHPSTORM remote_host=192.168.200.1 remote_port=9000"' >> /home/vagrant/.profile
#the server name should correspond to the server name you created in your project in phpstorm
echo 'export PHP_IDE_CONFIG="serverName=emission.local"' >> /home/vagrant/.profile

# Box shrink
/vagrant/post-provision.sh
echo 'Machine ready'
