#!/bin/bash -ex

COMPOSER_CMD="php -d memory_limit=-1 /usr/local/bin/composer"

## For local testing, uncomment these lines, then run the script directly.
# REPO="utdk_profile"
# BRANCH="123-branch"
# git clone https://github.com/utdk/$REPO.git
# cd $REPO

git fetch && git checkout develop
git fetch && git checkout $BRANCH
cp .github/workflows/.fixtures/syntax_checker.json composer.json
$COMPOSER_CMD install --ignore-platform-reqs
PHP_EXTENSIONS="php,inc,module,install,profile,yml"

PHP_LIST=$( git diff --name-only develop..$BRANCH -- "*.php" "*.inc" "*.yml" "*.module" "*.install" "*.profile")
if [ -z "$PHP_LIST" ]; then
  # No matching files in this pull request. Move on.
  exit 0
fi
vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/DrupalPractice/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS
vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS

# If a PHPCS violation has been found, send exit code to runner.
if [ $? -ne 0 ]; then
  exit 1
fi
