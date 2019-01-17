#!/bin/bash

# Set generic config
HOST=database
DATABASE=drupal8
PORT=3306
USER=root

GREEN='\033[0;32m'
DEFAULT_COLOR='\033[0;0m'

# Build the SQL prefix
SQLSTART="mysql -h $HOST -P $PORT -u $USER $DATABASE"

# Gather and destroy tables
TABLES=$($SQLSTART -e 'SHOW TABLES LIKE "cache%"' | awk '{ print $1}' | grep -v '^Tables' )
for t in $TABLES; do
  # DO db-specific wiping
  $SQLSTART -e "DROP TABLE $t"
done

echo ""
printf "${GREEN}Cache rebuild complete.${DEFAULT_COLOR}"
echo ""

## Remove Twig PHP cache (presumes a web docroot & sites/default setup)
rm -rf web/sites/default/files/php/twig/*
## Remove CSS & JS cached files.
rm -rf web/sites/default/files/css/*
rm -rf web/sites/default/files/js/*