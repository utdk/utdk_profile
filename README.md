# UT Drupal Kit 8
UT Drupal Kit is a collection of IT Architecture and Infrastructure Committee-endorsed resources for Drupal site developers. It aims to simplify development of websites on campus, standardize University brand templates, and improve accessibility while allowing developers to customize as needed. The code is downloadable at no cost for the UT community.

## Contents of this repository

This repository contains only the "kernel" of the customized code required to run UT Drupal Kit, version 8. It uses the dependency manager [Composer](https://getcomposer.org/) to retrieve the [Drops-8 codebase](https://github.com/pantheon-systems/drops-8), contributed projects from [drupal.org](https://drupal.org), and custom projects from separate Github repositories. Instructions for using Composer to build a fully-functional codebase are below.

## Dependencies for local development
For customization of UT Drupal Kit's codebase, the ability to run a replica of your site on a local machine is indispensable. The most straightforward way to do this is using Docker, and our recommended Docker based development utility is Docksal. For more information on setting up a local environment and running tests, see the [Docksal documentation](https://github.austin.utexas.edu/eis1-wcs/utdk8/blob/master/.docksal/README.md).

## How to use composer.json / composer.lock
*If you don't plan to use Composer to customize your codebase, this section does not apply.*

To allow individual developers to define their own Composer elements, we do not
commit `composer.json` & `composer.lock`. Instead, we commit equivalent "example"
files (which are converted to "real" files during the `setup.sh` script).

Developers using version control may then make changes to the `composer.json` file and commit the resulting customized file to their codebase without risking overwriting these changes when they update UT Drupal Kit in the future. During a UT Drupal Kit update, developers will want to "diff" the new `example.composer.json` file, and manually apply any changes to their own `composer.json` file, then run `composer update` and commit the resulting changes.