#!/bin/bash

# Currently, this file copies the example files listed below to 
# make them functional; this is so that downstream developers 
# may use their own versions of these files without introducing
# conflicts.

cp example.gitignore .gitignore
cp example.composer.json composer.json
cp example.pantheon.yml pantheon.yml
composer update -o
if [ ! -d "web/sites/default/config" ]; then
  mkdir web/sites/default/config
fi
