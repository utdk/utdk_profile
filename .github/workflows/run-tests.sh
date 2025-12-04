#!/bin/bash -ex

su -s /bin/bash -c '/var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/.github/workflows/.fixtures/phpunit.xml --stop-on-failure --stop-on-error --testdox /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/FunctionalJavascript' www-data

su -s /bin/bash -c '/var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/.github/workflows/.fixtures/phpunit.xml --stop-on-failure --stop-on-error --testdox /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/Functional' www-data


