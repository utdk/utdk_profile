#!/usr/bin/env bash
# This script allows passing of a drush alias
# to make the ULI properly load the web browser
# and offer potentially other advantages of using
# the native drush alias. Otherwise, it can be
# used with the @self fallback.
# Example usage, with local alias set to @utdk8-shared.local:
# bash install-dev-modules.sh @utdk8-shared.local
if [ -z "$1" ]
    then
    DRUSH_ALIAS="@self"
else
    DRUSH_ALIAS=$1
    echo $DRUSH_ALIAS
fi
cd web
drush $DRUSH_ALIAS en features_ui -y
drush $DRUSH_ALIAS en field_ui -y
drush $DRUSH_ALIAS en devel -y
drush $DRUSH_ALIAS en kint -y
drush $DRUSH_ALIAS en views_ui -y
drush $DRUSH_ALIAS en admin_toolbar -y
drush $DRUSH_ALIAS en admin_toolbar_tools -y
drush $DRUSH_ALIAS uli