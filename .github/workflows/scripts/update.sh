#!/bin/bash -ex

TERMINUS_CMD="$HOME/vendor/bin/terminus"
COMPOSER_CMD="php /usr/local/bin/composer"

# Enforce 11-charater limit on multidev names
MULTIDEV="${BRANCH:0:11}"

echo "Waking environment on Pantheon..."
$TERMINUS_CMD env:wake $SITE.$MULTIDEV
echo "Cloning repo from Pantheon..."
`$TERMINUS_CMD connection:info $SITE.dev --fields=git_command --format=string`
cd $SITE
git checkout $MULTIDEV

$COMPOSER_CMD update utexas/$REPO -W --ignore-platform-reqs

# Check for changes. If there are no changes, something went wrong.
if [ -z "$(git status --porcelain)" ]; then
  echo "Something went wrong, such as a Composer dependency conflict"
  echo "or the latest commit was not yet available on Github.com"
  exit 1
else
  git commit -m "Update to latest commit of $BRANCH branch" -a
  git push origin $MULTIDEV --force
fi

cd ..
echo "Cleaning up workspace..."
rm -rf $SITE
