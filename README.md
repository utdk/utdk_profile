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
0. Run `sh setup.sh`. This will copy the "example" files from the root into
respective usable files. It will then run `composer install`, which will
retrieve all packages needed for the distribution. The Drupal docroot will be
copied into the /web directory (and you will need to point your server to
this directory).
2. `cp web/sites/example.settings.local.php web/sites/default/settings.local.php`
3. Add database credentials to settings.local.php. Example:

```php
$databases['default']['default'] = [
  'database' => 'databasename',
  'username' => 'username',
  'password' => 'password',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];
```

4. `cd web/ && drush si -y`
5. You should now have a Drupal site installed, with the 'standard' profile!
6. Additional themes/modules, such as `layout_per_node` and `bootstrap` are
currently added but not enabled. Give 'em a try

# Making changes to composer.json / composer.lock (Distribution maintainers only)
To allow individual developers to define their own Composer elements, we do not
commit composer.json & composer.lock. Instead, we commit equivalent "example"
files (which are converted to "real" files during the `setup.sh` script).
Changes you want to introduce to the Composer files must be copied back
to the "example" equivalents. For example, after you run `composer require panels`
you would then need to run:

`cp composer.json example.composer.json`
`cp composer.lock example.composer.lock`

After this, `git status` will show Composer modifications you made in the
example files, and this is what you would commit to the repository
# Road Map
1. Create an installation profile within this repository. Updating the `$settings['install_profile']` in
settings.php to this installation profile will make it execute on a
`drush si -y`.
2. Determine which contributed and custom modules provided by this
composer.json should be placed in the profiles/`<custom-profile>`/ directory.
