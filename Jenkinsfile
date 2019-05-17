pipeline {
    options {
        disableConcurrentBuilds()
        ansiColor('xterm')
    }
    agent any
    environment {
        BROWSERTEST_OUTPUT_DIRECTORY = "/var/www/utdk8/web/sites/simpletest/browser_output"
        SIMPLETEST_BASE_URL          = "http://localhost:8080"
        SIMPLETEST_DB                = "mysql://root:utdk8ftw@utdk8_db:3306/utdk8"
        SYMFONY_DEPRECATIONS_HELPER  = "disabled"
    }
    stages {
        stage('Build') {
            steps {
                lock('docker-containers') {
                    checkout scm
                    script {
                        docker.image('mysql:5.7').withRun('-e "MYSQL_ROOT_PASSWORD=utdk8ftw" -e "MYSQL_DATABASE=utdk8"') { dbc ->
                            docker.image('mysql:5.7').inside("--link ${dbc.id}:utdk8_db -u root") {
                                sh '''
                                    /etc/init.d/mysql restart
                                '''
                            }
                            docker.image('circleci/php:7.2-apache-node-browsers').withRun('') { wc ->
                                docker.image('circleci/php:7.2-apache-node-browsers').inside("--link ${dbc.id}:utdk8_db -u root") {
                                    /*
                                     * Set-up web container and run tests.
                                     * Apt-get update first.
                                     */
                                    withCredentials([sshUserPrivateKey(credentialsId: '8df01156-ee87-4ac3-a2b2-c1629cbe82b4', keyFileVariable: 'KEY', passphraseVariable: 'PASS', usernameVariable: 'USER')]) {
                                        try {
                                            sh '''

                                                ## Set up ssh connections - need to connect to github.austin for composer managed dependencies...
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
                                                apt-get install mysql-client

                                                ## Install composer...
                                                curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
                                                export COMPOSER_ALLOW_SUPERUSER

                                                ## Setup vhost...
                                                cp -R $WORKSPACE/.pipeline-fixtures/utdk8-vhost.conf /etc/apache2/sites-available
                                                cp -R $WORKSPACE /var/www/utdk8
                                                #chown -R www-data:www-data /var/www/utdk8
                                                #chmod -R 774 /var/www/utdk8
                                                a2ensite utdk8-vhost.conf
                                                a2enmod rewrite
                                                service apache2 restart

                                                ## Set up site - install composer dependencies...
                                                cd /var/www/utdk8
                                                cp example.composer.json composer.json
                                                if [ ! -d "web/sites/default/config" ]; then
                                                    mkdir web/sites/default/config
                                                fi
                                                composer install --optimize-autoloader

                                                ## Ensure composer installed executables are in path...
                                                export PATH=/var/www/utdk8/vendor/bin:$PATH

                                                ## Set up drush...
                                                mkdir ~/.drush
                                                cp $WORKSPACE/.pipeline-fixtures/aliases.drushrc.php ~/.drush/aliases.drushrc.php

                                                #cp $WORKSPACE/.pipeline-fixtures/settings.php /var/www/utdk8/web/sites/default/settings.local.php

                                                ## Prepare for testing...
                                                if [ ! -d /var/www/utdk8/web/sites/simpletest/browser_output ]; then
                                                  mkdir -p /var/www/utdk8/web/sites/simpletest/browser_output
                                                fi
                                                chown -R www-data:www-data /var/www/utdk8
                                                chmod -R 774 /var/www/utdk8
                                                mkdir -p /tmp/test-results

                                                ## Start chromedriver in the background...
                                                chromedriver --whitelisted-ips=127.0.0.1 --headless &

                                                su -s /bin/bash -c '/var/www/utdk8/vendor/bin/phpunit -c /var/www/utdk8/web/core/phpunit.xml.dist --stop-on-failure --testsuite=functional --verbose --debug --group=utexas' www-data
                                                su -s /bin/bash -c '/var/www/utdk8/vendor/bin/phpunit -c /var/www/utdk8/.pipeline-fixtures/functional-js.phpunit.xml --stop-on-failure --testsuite=functional-javascript --verbose --debug --group=utexas' www-data

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
                                                #mysql -h utdk8_db -u root -p"utdk8ftw"                               

                                            '''
                                        }
                                        catch (exc) {
                                            sh '''
                                                cp -R /var/www/utdk8/web/sites/simpletest/browser_output $WORKSPACE/browser_output
                                                chown -R 995:1001 $WORKSPACE/browser_output
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
            archiveArtifacts '$WORKSPACE/browser_output/**'
        }
        failure {
            archiveArtifacts '$WORKSPACE/browser_output/**'
        }
    }
}