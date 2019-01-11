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
  echo "Dropping $t table from $DATABASE database..."
  # DO db-specific wiping
  $SQLSTART -e "DROP TABLE $t"
done

echo ""
printf "${GREEN}Cache cleared!${DEFAULT_COLOR}"
echo ""