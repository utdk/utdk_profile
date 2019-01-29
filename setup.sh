# Currently, this file simply copies "example." files in the docroot to
# non-example equivalents. Run "sh setup.sh" and then "composer install"
# In the future, it might be used for additional things!

git config core.hooksPath web/profiles/utexas/tests/git-hooks
cp example.gitignore .gitignore
cp example.composer.json composer.json
cp example.pantheon.yml pantheon.yml
cp example.circle.yml circle.yml
composer update -o
if [ ! -d "web/sites/default/config" ]; then
  mkdir web/sites/default/config
fi
