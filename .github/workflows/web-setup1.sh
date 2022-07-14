#!/bin/bash -ex

apt-get update
apt-get install -y libpng-dev
apt-get install -y libnss3
apt-get install -y zip
apt-get install -y git
apt-get install -y openssh-client
docker-php-ext-install gd
docker-php-ext-install mysqli pdo pdo_mysql
apt-get install mariadb-client -y

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
export COMPOSER_ALLOW_SUPERUSER

# Ensure user/group is same as github user on host so cleanup is possible...
usermod -u 993 www-data
groupmod -g 988 www-data

