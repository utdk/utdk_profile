pipeline {
    options {
        disableConcurrentBuilds()
        ansiColor('xterm')
        quietPeriod(20)
    }
    agent any
    environment {
        BROWSERTEST_OUTPUT_DIRECTORY = "/var/www/utdk_scaffold/web/sites/simpletest/browser_output"
        SIMPLETEST_BASE_URL          = "http://localhost:8080"
        SIMPLETEST_DB                = "mysql://root:utdkftw@utdk_db:3306/utdk"
        SYMFONY_DEPRECATIONS_HELPER  = "disabled"
    }
    stages {
        stage('Build') {
            steps {
                lock('docker-containers') {
                    script {
                        docker.image('mysql:5.7').withRun('-e "MYSQL_ROOT_PASSWORD=utdkftw" -e "MYSQL_DATABASE=utdk"') { dbc ->
                            docker.image('mysql:5.7').inside("--link ${dbc.id}:utdk_db -u root") {
                                sh '''
                                    /etc/init.d/mysql restart
                                '''
                            }
                            docker.image('circleci/php:7.4-apache-node-browsers').withRun('') { wc ->
                                docker.image('circleci/php:7.4-apache-node-browsers').inside("--link ${dbc.id}:utdk_db -u root") {
                                    /*
                                     * Set-up web container and run tests.
                                     * Apt-get update first.
                                     */
                                    withCredentials([sshUserPrivateKey(credentialsId: '8df01156-ee87-4ac3-a2b2-c1629cbe82b4', keyFileVariable: 'KEY', passphraseVariable: 'PASS', usernameVariable: 'USER')]) {
                                        try {
                                            sh '''
                                                ## Set up ssh connections to connect to github.austin...
                                                eval `ssh-agent`
                                                ssh-add $KEY
                                                # Pipe the output to true to avoid failing the build for false error.
                                                ssh -vv -T -o StrictHostKeyChecking=no -i $KEY git@github.austin.utexas.edu || true

                                                ## Install things...
                                                rm -rf /var/lib/apt/lists/*
                                                #add-apt-repository ppa:ondrej/php
                                                apt-get update
                                                apt-get install -y libpng-dev
                                                #apt-get install -y php7.2-mysql
                                                docker-php-ext-install gd
                                                docker-php-ext-install mysqli pdo pdo_mysql
                                                apt-get install mariadb-client

                                                ## Install composer...
                                                curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
                                                export COMPOSER_ALLOW_SUPERUSER

                                                ### Uncomment lines 56-58 to download/install specific version of chromedriver
                                                #wget https://chromedriver.storage.googleapis.com/74.0.3729.6/chromedriver_linux64.zip
                                                #unzip chromedriver_linux64.zip
                                                #mv chromedriver /usr/local/bin/chromedriver

                                                ## Remove any remnants of previous builds...
                                                if [ -d /var/www/utdk_scaffold ]; then
                                                    rm -rf /var/www/utdk_scaffold
                                                fi
                                                cd /var/www
                                                php -d memory_limit=-1 /usr/local/bin/composer create-project utexas/utdk-project utdk_scaffold --stability=dev --remove-vcs --no-install
                                                cd /var/www/utdk_scaffold/upstream-configuration
                                                php -d memory_limit=-1 /usr/local/bin/composer remove utexas/utdk_profile --no-update

                                                cd /var/www/utdk_scaffold
                                                php -d memory_limit=-1 /usr/local/bin/composer config repositories.utdk_profile vcs git@github.austin.utexas.edu:eis1-wcs/utdk_profile.git
                                                php -d memory_limit=-1 /usr/local/bin/composer config repositories.forty_acres vcs git@github.austin.utexas.edu:eis1-wcs/forty_acres.git
                                                php -d memory_limit=-1 /usr/local/bin/composer config repositories.utexas_qualtrics_filter vcs git@github.austin.utexas.edu:eis1-wcs/utexas_qualtrics_filter.git
                                                php -d memory_limit=-1 /usr/local/bin/composer config repositories.utdk_localdev vcs git@github.austin.utexas.edu:eis1-wcs/utdk_localdev.git

                                                php -d memory_limit=-1 /usr/local/bin/composer clear-cache
                                                php -d memory_limit=-1 /usr/local/bin/composer require utexas/utdk_profile:"dev-$GIT_BRANCH"  --no-update
                                                ## Requirements defined in utdk_localdev needed to run test suite, but not other setup steps of dev-scaffold script.
                                                php -d memory_limit=-1 /usr/local/bin/composer require utexas/utdk_localdev:dev-master --dev

                                                ## Ensure composer installed executables are in path...
                                                export PATH=/var/www/utdk_scaffold/vendor/bin:$PATH

                                                ## Set up drush...
                                                mkdir ~/.drush
                                                cp $WORKSPACE/.pipeline-fixtures/aliases.drushrc.php ~/.drush/aliases.drushrc.php

                                                ## Setup vhost...
                                                cp -R $WORKSPACE/.pipeline-fixtures/utdk-vhost.conf /etc/apache2/sites-available
                                                a2ensite utdk-vhost.conf
                                                a2enmod rewrite
                                                service apache2 restart

                                                ## Prepare for testing...
                                                mkdir -p $BROWSERTEST_OUTPUT_DIRECTORY
                                                chown -R www-data:www-data /var/www/utdk_scaffold
                                                cp /var/www/utdk_scaffold/.docksal/drupal/patched/menu_block_test.info.yml /var/www/utdk_scaffold/web/modules/contrib/menu_block/tests/modules/menu_block_test/menu_block_test.info.yml
                                                chmod -R 774 /var/www/utdk_scaffold
                                                mkdir -p /tmp/test-results

                                                ## Start chromedriver in the background...
                                                chromedriver --whitelisted-ips=127.0.0.1 --headless &

                                                ## Run tests...
                                                su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/utdk_scaffold/vendor/bin/phpunit -c $WORKSPACE/.pipeline-fixtures/functional-js.phpunit.xml --stop-on-failure --testsuite=functional-javascript --verbose --debug --group=utexas' www-data
                                                su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/utdk_scaffold/vendor/bin/phpunit -c /var/www/utdk_scaffold/web/core/phpunit.xml.dist --stop-on-failure --testsuite=functional --verbose --debug --group=utexas' www-data
                                                su -s /bin/bash -c 'BROWSERTEST_CACHE_DB=1 /var/www/utdk_scaffold/vendor/bin/phpunit -c /var/www/utdk_scaffold/web/core/phpunit.xml.dist --stop-on-failure --testsuite=unit --verbose --debug --group=utexas' www-data

                                                ### Debug steps ###
                                                ### Uncomment lines 97 - 112 to help ensure environment and site are working as expected.
                                                # Is Apache running?
                                                #ps aux | grep apache
                                                # Does the site respond with a 200?
                                                #http_status=$(curl -I -s  http://localhost:8080 | grep HTTP)
                                                #echo "$http_status"
                                                #if [[ "$http_status" != *"200"* ]]; then
                                                #    exit 1
                                                #fi
                                                # Can you connect to the MYSQL container?
                                                #mysql -h utdk_db -u root -p"utdkftw"

                                            '''
                                        }
                                        catch (exc) {
                                            sh '''
                                                if [ -d $BROWSERTEST_OUTPUT_DIRECTORY ]; then
                                                    cp -R $BROWSERTEST_OUTPUT_DIRECTORY $WORKSPACE/browser_output
                                                    chown -R 995:1001 $WORKSPACE/browser_output
                                                fi
                                                exit 1
                                            '''
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    post {
        always {
            sh '''
                docker system prune -a -f
                docker volume prune -f
            '''
        }
        success {
            echo 'Success!'
        }
        unstable {
            archiveArtifacts 'browser_output/**'
        }
        failure {
            archiveArtifacts 'browser_output/**'
        }
    }
}
