#!/bin/bash -ex

set -e

COMPOSER_CMD="php -d memory_limit=-1 /usr/local/bin/composer"
TOOLING=".github/workflows/.fixtures/"
REPO="utdk_profile"
HOST="github.austin.utexas.edu"
OWNER="eis1-wcs"
WORKINGDIR="syntax/"

mkdir -p $WORKINGDIR
cd $WORKINGDIR
# Clean up cloned repository before starting.
if [ -d $REPO ]; then
  rm -rf $REPO
fi

# Authenticate to gh cli
echo $TOKEN | gh auth login --hostname $HOST --with-token
gh auth setup-git --hostname $HOST
gh repo clone $OWNER/$REPO
cd $REPO

# GitHub Actions performs a 'shallow' fetch, so we get more history with --depth
# See https://stackoverflow.com/a/59570673/6046296
git checkout -f
git fetch --depth=100 && git checkout develop
git fetch --depth=100 && git checkout $BRANCH

$COMPOSER_CMD validate --no-check-all
# If there are composer validation issues, this will send an exit code of 1.

# Limit to where this branch diverged...
# https://git-scm.com/docs/git-merge-base#_discussion
TO_MERGE=$(git merge-base develop HEAD)

PHP_EXTENSIONS="php,inc,module,install,profile,theme,yml"
PHP_LIST=$( git diff $TO_MERGE --name-only --diff-filter=ACMRX -- "*.php" "*.inc" "*.yml" "*.module" "*.install" "*.profile" "*.theme")
if [ ! -z "$PHP_LIST" ]; then
  echo "*** Changed PHP files ****"
  echo $PHP_LIST
  cp $TOOLING/php_checker.json composer.json
  $COMPOSER_CMD install --ignore-platform-reqs
  EXCLUDE_RULES="Drupal.InfoFiles.AutoAddedKeys,DrupalPractice.Objects.GlobalDrupal,DrupalPractice.FunctionCalls.InsecureUnserialize"
  vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/DrupalPractice/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS --exclude=$EXCLUDE_RULES
  vendor/bin/phpcs --standard="vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml" $PHP_LIST --extensions=$PHP_EXTENSIONS --exclude=$EXCLUDE_RULES
fi

JS_LIST=$( git diff $TO_MERGE --name-only --diff-filter=ACMRX -- "*.js")
if [ ! -z "$JS_LIST" ]; then
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
  export NVM_DIR="$HOME/.nvm"
  [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
  cp $TOOLING/js_checker.json package.json
  nvm install 20 && nvm use 20
  npm install
  echo "*** Changed JS files ****"
  echo $JS_LIST
  echo "*** Linting... ***"
  npx eslint $JS_LIST
fi

# Clean up before exiting.
rm -rf $REPO

# If a PHPCS violation has been found, send exit code to runner.
if [ $? -ne 0 ]; then
  echo "CODE SYNTAX VIOLATION(S) FOUND"
  exit 1
fi
