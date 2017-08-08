#!/bin/bash
#
# Currently, this file simply copies "example." files in the docroot to
# non-example equivalents. Run "setup.sh" and then "composer install"
# In the future, it might be used for additional things!

cp example.gitignore .gitignore
cp example.composer.json composer.json
cp example.composer.lock composer.lock
cp example.pantheon.yml pantheon.yml
cp example.circle.yml circle.yml
