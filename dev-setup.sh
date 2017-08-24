#!/bin/bash
#
# This script is mostly equivalent to setup.sh
# The difference between this script
# and setup.sh is that this does NOT copy the example.composer.lock.
# Rather, it removes any present composer.lock, and then updates the
# present example.composer.lock. This is to allow
# composer dependencies we have set to "dev-master" that do not have
# an explicit version are updated as needed by composer when testing,
# and ultimately reduces the extra step of updating the example.composer.json
# when the time comes to update it.

cp example.gitignore .gitignore
cp example.composer.json composer.json
rm composer.lock
cp example.pantheon.yml pantheon.yml
cp example.circle.yml circle.yml
composer install
cp composer.lock example.composer.lock
echo "Updated example.composer.lock."
