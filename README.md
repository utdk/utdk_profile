# UT Drupal Kit 8.x-1.x-dev
This is (currently) a proof-of-concept for Composer-based package management of
a Drupal 8 distribution that dynamically pulls in the Drupal 8 codebase and
contributed projects from Packagist, as well as custom projects from Github
repositories.

It is meant to be a tool for collaborative development of custom functionality
that is housed in *other* repositories. Developers can use this repository to
install a single site that orchestrates these actively developed projects,
rather than having to maintain separate local Drupal instances or do multiple
git clones.

# Development Setup
1. In the root directory, run `composer install`. This will retrieve all
packages needed for the distribution. The Drupal docroot will be copied into the
/web directory (and you will need to point your server to this directory).
2. `cp web/sites/example.settings.local.php web/sites/default/settings.local.php`
3. Add database credentials to settings.local.php
4. `cd web/ && `drush si -y`
5. You should now have a Drupal site installed, with the 'standard' profile!
6. Additional themes/modules, such as `layout_per_node` and `bootstrap` are
currently added but not enabled. Give 'em a try

# Road Map
1. Create an installation profile in a separate repo and pull it in via this
repository's composer.json file. Updating the `$settings['install_profile']` in
settings.php to this installation profile will make it execute on a
`drush si -y`.
2. Determine whether contributed and custom modules provided by this
composer.json should be placed in the profiles/<custom-profile>/ directory.
