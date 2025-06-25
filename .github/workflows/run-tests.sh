#!/bin/bash -ex

## Run tests...
su -s /bin/bash -c '/var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/utdk-project/web/core/phpunit.xml.dist --testdox /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/Functional' www-data
su -s /bin/bash -c '/var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/.github/workflows/.fixtures/functional-js.phpunit.xml --testdox /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/FunctionalJavascript' www-data

