#!/usr/bin/env bash

drush @utdk8-shared.local en features_ui -y
drush @utdk8-shared.local en field_ui -y
drush @utdk8-shared.local en devel -y
drush @utdk8-shared.local en kint -y
drush @utdk8-shared.local en views_ui -y
drush @utdk8-shared.local en admin_toolbar -y
drush @utdk8-shared.local en admin_toolbar_tools -y
drush @utdk8-shared.local uli