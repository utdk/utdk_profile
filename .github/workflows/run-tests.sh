#!/bin/bash -ex

## Run tests...
su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/.github/workflows/.fixtures/functional-js.phpunit.xml --stop-on-failure --testsuite=functional-javascript --verbose --debug /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/FunctionalJavascript' www-data
su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/tests/utdk-project/vendor/bin/phpunit -c /var/www/tests/utdk-project/web/core/phpunit.xml.dist --stop-on-failure --testsuite=functional --verbose --debug /var/www/tests/utdk-project/web/profiles/custom/utexas/tests/src/Functional' www-data
