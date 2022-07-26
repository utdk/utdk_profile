#!/bin/bash -ex

rm -rf /var/www/html/utdk_scaffold/web/profiles/custom/utexas/tests/src/FunctionalJavascript/SiteAnnouncementTest.php
rm -rf /var/www/html/utdk_scaffold/web/profiles/custom/utexas/tests/src/FunctionalJavascript/WidgetsTest.php

## Run tests...
su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/html/utdk_scaffold/vendor/bin/phpunit -c /var/www/html/.github/workflows/.fixtures/functional-js.phpunit.xml --stop-on-failure --testsuite=functional-javascript --verbose --debug --group=utexas' www-data
su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/html/utdk_scaffold/vendor/bin/phpunit -c /var/www/html/utdk_scaffold/web/core/phpunit.xml.dist --stop-on-failure --testsuite=functional --verbose --debug --group=utexas' www-data
su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/html/utdk_scaffold/vendor/bin/phpunit -c /var/www/html/utdk_scaffold/web/core/phpunit.xml.dist --stop-on-failure --testsuite=unit --verbose --debug --group=utexas' www-data
