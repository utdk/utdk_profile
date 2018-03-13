# UT Drupal Kit 8
UT Drupal Kit is a collection of IT Architecture and Infrastructure Committee-endorsed resources for Drupal site developers. It aims to simplify development of websites on campus, standardize University brand templates, and improve accessibility while allowing developers to customize as needed. The code is downloadable at no cost for the UT community.

## Contents of this repository

This repository contains only the "kernel" of the customized code required to run UT Drupal Kit, version 8. It uses the dependency manager [Composer](https://getcomposer.org/) to retrieve the [Drops-8 codebase](https://github.com/pantheon-systems/drops-8), contributed projects from [drupal.org](https://drupal.org), and custom projects from separate Github repositories. Instructions for using Composer to build a fully-functional codebase are below.

## Dependencies for local development
For customization of UT Drupal Kit's codebase, the ability to run a replica of your site on a local machine is indispensable. Since Drupal is written in PHP and uses an SQL database, that means you'll need:
- PHP 5.5.9 or higher. See [Drupal 8 PHP versions supported](https://www.drupal.org/docs/8/system-requirements/drupal-8-php-requirements).
- A database server (MySQL, PostgreSQL, or SQLlite that meets the [minimum Drupal 8 requirements](https://www.drupal.org/docs/8/system-requirements/database-server)).
- A webserver that meets the minimum PHP requirements above. Typically, this means Apache, Nginx, or Microsoft IIS. See [Drupal webserver requirements](https://www.drupal.org/docs/8/system-requirements/web-server).

There are a number of pre-packaged solutions that simplify setup of the above. These includes [MAMP](https://www.mamp.info/en/), [Valet](https://laravel.com/docs/5.6/valet), and [Lando](https://docs.devwithlando.io/).

Finally, you will need to install [Composer](https://getcomposer.org/doc/00-intro.md), the PHP dependency manager.

Not required -- but highly recommended -- is the command-line shell for Drupal, [drush](http://www.drush.org/). 

If local web development is new to you, consider coming by Web Publishing Office Hours for setup assistance.
 

## Development Setup
Once you have Composer installed, and a local web server up and running, the following steps will get you to a freshly installed Drupal Kit site:

0. Within this codebase's document root, run `sh setup.sh`. This will copy "example" files from into usable files. It will then run `composer install`, which will retrieve all packages needed for the distribution. The Drupal document root will be copied into the `/web` directory (and you will need to point your server to
that directory). See [web docroot background](https://www.drupal.org/node/2767907).
2. `cp web/sites/example.settings.local.php web/sites/default/settings.local.php`
3. Create a MySQL database, then add its connection credentials to the newly created `settings.local.php`. Example:

```php
$databases['default']['default'] = [
  'database' => 'MYSQL_DATABASE',
  'username' => 'MYSQL_USERNAME',
  'password' => 'MYSQL_PASSWORD',
  'host' => 'localhost',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];
```

4. Either navigate to your local site's domain and follow the web-based installation instructions, or if you prefer to use `drush`, then `cd web/` and run the drush [site-install](https://drushcommands.com/drush-8x/core/site-install/) command; note that it is recommended to pass the `--account-mail` and `--site-mail` parameters along, with valid email addresses.
5. You should now have a Drupal site installed, with the "UTexas" profile!


## How to use composer.json / composer.lock
*If you don't plan to use Composer to customize your codebase, this section does not apply.*

To allow individual developers to define their own Composer elements, we do not
commit `composer.json` & `composer.lock`. Instead, we commit equivalent "example"
files (which are converted to "real" files during the `setup.sh` script). 

Developers using version control may then make changes to the `composer.json` file and commit the resulting customized file to their codebase without risking overwriting these changes when they update UT Drupal Kit in the future. During a UT Drupal Kit update, developers will want to "diff" the new `example.composer.json` file, and manually apply any changes to their own `composer.json` file, then run `composer update` and commit the resulting changes.



