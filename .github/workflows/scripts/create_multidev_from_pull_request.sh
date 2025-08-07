#!/bin/bash -ex

TERMINUS_CMD="$HOME/vendor/bin/terminus"
COMPOSER_CMD="php -d memory_limit=-1 /usr/local/bin/composer"

# Enforce 11-charater limit on multidev names
MULTIDEV="${BRANCH:0:11}"

get_latest_tag() {
  if [[ $1 = utexas ]];then
    url="https://raw.githubusercontent.com/utdk/utdk_profile/develop/utexas.info.yml"
    file="utexas.info.yml"
  else
    url="https://raw.githubusercontent.com/utdk/$1/develop/$1.info.yml"
    file="$1.info.yml"
  fi
  curl $url -o file.info.yml
  while read line; do
    if [[ $line =~ version: ]];then
      version="${line//version: /}"
    fi
  done < file.info.yml
  echo $version | sed "s/'//g"
  rm file.info.yml
}

## Get the tag referenced for forty_acres within utdk_profile.
## This is the 'common denominator' that needs to be the Composer alias
## so that the develop branches can be pulled in.
FORTYACRES=$(get_latest_tag forty_acres)
UTDKPROFILE=$(get_latest_tag utexas)
UTEVENT=$(get_latest_tag utevent)
UTNEWS=$(get_latest_tag utnews)
UTPROF=$(get_latest_tag utprof)

# Clean up anything from previous runs
rm -rf utdk-project
rm -rf utdk_profile
rm -rf utdkpr

# Clone a fresh version of the utdk-project repository.
git clone https://github.com/utdk/utdk-project.git
cd utdk-project
git checkout -b $MULTIDEV

# Add the Pantheon remote for utdkpr.
GIT_URL=$($TERMINUS_CMD connection:info $SITE.dev --fields=git_url --format=string)
git remote add pantheon $GIT_URL

(cd upstream-configuration && $COMPOSER_CMD remove utexas/utdk_profile --no-update)
### THIS IS THE OPERATIVE CHANGE. USE A BRANCH FROM UTDK_PROFILE ###
$COMPOSER_CMD require utexas/utdk_profile "dev-$BRANCH as $UTDKPROFILE" --no-update

## Use inline aliases to reduce the likelihood of dependency conflicts.
$COMPOSER_CMD require utexas/forty_acres:"dev-develop as $FORTYACRES" --no-update
$COMPOSER_CMD require utexas/utevent:"dev-develop as $UTEVENT" --no-update
$COMPOSER_CMD require utexas/utnews:"dev-develop as $UTNEWS" --no-update
$COMPOSER_CMD require utexas/utprof:"dev-develop as $UTPROF" --no-update
$COMPOSER_CMD require utexas/utdk_saas:dev-develop --no-update

$COMPOSER_CMD install --ignore-platform-reqs
if [ -f composer.lock ]; then
  git add .
  git commit -m "Deploy a site from branch $BRANCH" -a
  git push pantheon $MULTIDEV --force
  sleep 120
  $TERMINUS_CMD env:create $SITE.dev $MULTIDEV
else
  echo "Something went wrong, such as a Composer dependency conflict."
  exit 1
fi

PANTHEON_SITE_NAME="utdkpr"
ACCOUNT_NAME="pr-tester"
ACCOUNT_MAIL="wcs-drupal-site-admins@utlists.utexas.edu"
SITE_EMAIL="wcs-drupal-site-admins@utlists.utexas.edu"
SITE_NAME="Pull Request Tester"
USERS="jdt947 jmf3658 pfg dra68 bjc2265 mmarler"

# Enforce 11-charater limit on multidev names
CMD="$TERMINUS_CMD remote:drush $SITE.$MULTIDEV"

echo "Installing a fresh version of the site..."
$CMD -- site:install utexas -y --account-name="$ACCOUNT_NAME" --account-mail="$ACCOUNT_MAIL" --site-mail="$SITE_EMAIL" --site-name="$SITE_NAME" utexas_installation_options.default_content=1 install_configure_form.enable_update_status_module=NULL

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

echo "Creating users..."
USER_ARRAY=($USERS)
for USER in "${USER_ARRAY[@]}"
do
  $CMD -- user:create $USER
  $CMD -- user:role:add utexas_content_editor $USER
  $CMD -- user:role:add utexas_site_manager $USER
done

$CMD -- cr
