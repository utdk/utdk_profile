#!/bin/bash -ex

apt-get update
apt-get install -y \
    git \
    openssh-client \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libnss3 \
    libzip-dev \
    zip
## Add php-zip to compress files for uploads to selenium container in tests.
docker-php-ext-install zip
## Configure image protocol per 3280795#comment-15221199.
docker-php-ext-install -j$(nproc) gd
docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg
docker-php-ext-install gd
docker-php-ext-install mysqli pdo pdo_mysql
apt-get install mariadb-client -y

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
export COMPOSER_ALLOW_SUPERUSER

# Ensure user/group is same as github user on host so cleanup is possible...
# NOTE: THESE IDS ARE SPECIFIC TO THE P02 VIRTUAL MACHINE AND WILL NOT WORK ON
# THE T01 MACHINE. SEE https://wikis.utexas.edu/display/WCMS/GitHub+Actions
usermod -u 994 www-data
groupmod -g 989 www-data

