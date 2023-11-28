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
$COMPOSER_CMD require utexas/pantheon_saml_integration:"dev-develop as 4.0.0-alpha4" --no-update
$COMPOSER_CMD require utexas/utdk_saas:dev-develop --no-update

$COMPOSER_CMD install --ignore-platform-reqs
if [ -f composer.lock ]; then
  git add .
  git commit -m "Deploy a site from branch $BRANCH" -a
  git push pantheon $MULTIDEV --force
  sleep 60
  $TERMINUS_CMD env:create $SITE.dev $MULTIDEV
else
  echo "Something went wrong, such as a Composer dependency conflict."
  exit 1
fi
