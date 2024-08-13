#!/bin/bash -ex

## Ensure composer installed executables are in path...
export PATH=/var/www/html/utdk_scaffold/vendor/bin:$PATH
## Set up drush...
mkdir ~/.drush
cp /var/www/html/.github/workflows/.fixtures/aliases.drushrc.php ~/.drush/aliases.drushrc.php
## Setup vhost...
cp -R /var/www/html/.github/workflows/.fixtures/utdk-vhost.conf /etc/apache2/sites-available
a2ensite utdk-vhost.conf
a2enmod rewrite
#service apache2 restart
service apache2 reload

## Prepare for testing...
mkdir -p $BROWSERTEST_OUTPUT_DIRECTORY
chown -R www-data:www-data /var/www/html/utdk_scaffold
chown -R www-data:www-data /var/www/html/.github/workflows/.fixtures
chmod -R 774 /var/www/html/utdk_scaffold
mkdir -p /tmp/test-results
