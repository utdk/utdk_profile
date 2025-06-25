#!/bin/bash -ex

## Ensure composer installed executables are in path...
export PATH=/var/www/tests/utdk-project/vendor/bin:$PATH
## Set up drush...
mkdir ~/.drush
## Setup vhost...
cp -R /var/www/tests/.github/workflows/.fixtures/utdk-vhost.conf /etc/apache2/sites-available
a2ensite utdk-vhost.conf
a2enmod rewrite
#service apache2 restart
service apache2 reload

## Prepare for testing...
mkdir -p $BROWSERTEST_OUTPUT_DIRECTORY
chown -R www-data:www-data /var/www/tests/utdk-project
chown -R www-data:www-data /var/www/tests/.github/workflows/.fixtures
chmod -R 774 /var/www/tests/utdk-project
mkdir -p /tmp/test-results
