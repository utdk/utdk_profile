#!/bin/bash -ex

COMPOSER_CMD="php -d memory_limit=-1 /usr/local/bin/composer"

## For local testing, uncomment these lines, then run the script directly.
# REPO="utdk_profile"
# BRANCH="123-branch"
# git clone https://github.com/utdk/$REPO.git
# cd $REPO

# GitHub Actions performs a 'shallow' fetch, so we get more history with --depth
# See https://stackoverflow.com/a/59570673/6046296
git checkout -f
git fetch --depth=100 && git checkout develop
git fetch --depth=100 && git checkout $BRANCH

$COMPOSER_CMD validate --no-check-all
# If there are composer validation issues, this will send an exit code of 1.

cp .github/workflows/.fixtures/syntax_checker.json composer.json
$COMPOSER_CMD install --ignore-platform-reqs
EXCLUDE_RULES="Drupal.InfoFiles.AutoAddedKeys,DrupalPractice.Objects.GlobalDrupal,DrupalPractice.FunctionCalls.InsecureUnserialize"
PHP_EXTENSIONS="php,inc,module,install,profile,yml"
# Limit to where this branch diverged...
# https://git-scm.com/docs/git-merge-base#_discussion
TO_MERGE=$(git merge-base develop HEAD)
PHP_LIST=$( git diff $TO_MERGE --name-only --diff-filter=ACMRX -- "*.php" "*.inc" "*.yml" "*.module" "*.install" "*.profile")
if [ -z "$PHP_LIST" ]; then
  # No matching files in this pull request. Move on.
  exit 0
fi
vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/DrupalPractice/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS --exclude=Drupal.InfoFiles.AutoAddedKeys
vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS --exclude=$EXCLUDE_RULES

# If a PHPCS violation has been found, send exit code to runner.
if [ $? -ne 0 ]; then
  exit 1
fi
