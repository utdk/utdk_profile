#!/bin/bash -ex

COMPOSER="php -d memory_limit=-1 /usr/local/bin/composer"

# Authenticate to gh cli
echo $TOKEN | gh auth login --hostname $HOST --with-token
gh auth setup-git --hostname $HOST

# Get the latest tags for forty_acres
# This needs to be the Composer alias
# so that the develop branches can be pulled in.
FORTYACRES=$(gh api repos/$OWNER/forty_acres/tags --jq '.[0].name' --hostname $HOST)
# Install stuff for UTDK site with composer...
# Executed on host/Github Action Runner VM for ease of management of access to protected resources, file permissions, and performance...
$COMPOSER create-project utexas/utdk-project:dev-develop utdk_scaffold --stability=dev --remove-vcs --no-install
cd utdk_scaffold/upstream-configuration
$COMPOSER remove utexas/utdk_profile --no-update
cd ..
$COMPOSER config repositories.utdk_profile vcs git@github.austin.utexas.edu:eis1-wcs/utdk_profile.git
$COMPOSER config repositories.forty_acres vcs git@github.austin.utexas.edu:eis1-wcs/forty_acres.git
$COMPOSER config repositories.utexas_qualtrics_filter vcs git@github.austin.utexas.edu:eis1-wcs/utexas_qualtrics_filter.git
$COMPOSER config repositories.utdk_localdev vcs git@github.austin.utexas.edu:eis1-wcs/utdk_localdev.git
$COMPOSER config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
$COMPOSER config --no-plugins allow-plugins.phpstan/extension-installer true

$COMPOSER clear-cache
$COMPOSER require utexas/utdk_profile:"dev-$BRANCH"  --no-update
$COMPOSER require utexas/forty_acres:"dev-develop as $FORTYACRES" --no-update
# Pin to Drush 12 while on PHP 8.1
composer require drush/drush:^12 --no-update
$COMPOSER require "drupal/core-dev":"^10" --ignore-platform-reqs
cat composer.json