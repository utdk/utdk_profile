#!/bin/bash -ex

TERMINUS_CMD="$HOME/vendor/bin/terminus"
PANTHEON_SITE_NAME="utdkpr"
ACCOUNT_NAME="pr-tester"
ACCOUNT_MAIL="wcs-drupal-site-admins@utlists.utexas.edu"
SITE_EMAIL="wcs-drupal-site-admins@utlists.utexas.edu"
SITE_NAME="Pull Request Tester"
USERS="jdt947 jmf3658 rh34438 pfg dra68 bjc2265 mmarler"

# Enforce 11-charater limit on multidev names
MULTIDEV="${BRANCH:0:11}"
CMD="$TERMINUS_CMD remote:drush $SITE.$MULTIDEV"

echo "Installing a fresh version of the site..."
$TERMINUS_CMD remote:drush $SITE.$MULTIDEV -- site:install utexas -y --account-name="$ACCOUNT_NAME" --account-mail="$ACCOUNT_MAIL" --site-mail="$SITE_EMAIL" --site-name="$SITE_NAME" utexas_installation_options.default_content=1 install_configure_form.enable_update_status_module=NULL

echo "Enabling default roles..."
$CMD -- en utexas_role_site_manager -y
$CMD -- utdk_profile:grant --set=manager --role=utexas_site_manager

echo "Enabling utdk_saas components..."
$CMD -- en utdk_saas -y

echo "Setting up Profile add-on..."
$CMD -- -y en utprof utprof_block_type_profile_listing utprof_content_type_profile utprof_view_profiles utprof_vocabulary_groups utprof_vocabulary_tags utprof_demo_content
$CMD -- utprof:grant --set=manager --role=utexas_site_manager
$CMD -- utprof:grant --set=editor --role=utexas_content_editor

echo "Setting up News add-on..."
$CMD -- -y en utnews utnews_block_type_news_listing utnews_content_type_news utnews_demo_content utnews_view_listing_page utnews_vocabulary_authors utnews_vocabulary_categories utnews_vocabulary_tags
$CMD -- utnews:grant --set=manager --role=utexas_site_manager
$CMD -- utnews:grant --set=editor --role=utexas_content_editor

echo "Setting up Event add-on..."
$CMD -- -y en utevent utevent_block_type_event_listing utevent_content_type_event utevent_demo_content utevent_view_listing_page utevent_vocabulary_location utevent_vocabulary_tags
$CMD -- utevent:grant --set=manager --role=utexas_site_manager
$CMD -- utevent:grant --set=editor --role=utexas_content_editor

echo "Setting up SAML integration..."
$CMD -- en utexas_saml_auth_helper -y
$CMD -- config:set simplesamlphp_auth.settings activate 1 -y

echo "Creating users..."
USER_ARRAY=($USERS)
for USER in "${USER_ARRAY[@]}"
do
  $CMD -- user:create $USER
  $CMD -- user:role:add utexas_content_editor $USER
  $CMD -- user:role:add utexas_site_manager $USER
done
$CMD -- saml-convert all -y

$CMD -- cr
