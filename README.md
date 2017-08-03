# UT Drupal Kit 8.x-1.x-dev
This is (currently) a proof-of-concept for Composer-based package management of
a Drupal 8 distribution that dynamically pulls in the Drupal 8 codebase and
contributed projects from Packagist, as well as custom projects from Github
repositories.

# Development Setup
1. In the root directory, run `composer install`. This will retrieve all
packages needed for the distribution. The Drupal docroot will be copied into the
/web directory (and you will need to point your server to this directory).
2. `cp web/sites/example.settings.local.php web/sites/default/settings.local.php`
3. Add database credentials to settings.local.php
4. `cd web/ && `drush si -y`
5. You should now have a Drupal site installed, with the 'standard' profile!
