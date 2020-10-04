#!/bin/sh

set -e

echo ""
echo "########## INITIALIZING APPLICATION ##########"
PROJECT_PATH=/var/www/html/

# install dependencies
if [ ! -f ${PROJECT_PATH}composer.done -o ${PROJECT_PATH}composer.lock -nt ${PROJECT_PATH}composer.done ]; then
##composer install --no-dev
  echo "run composer install"
  composer install
  touch ${PROJECT_PATH}composer.done
else
  echo "composer is up to date"
fi

# start media player daemon
if [ $(service mpd status | grep running | wc -l) -lt "1" ]
then
    echo "MPD not running - going to start"
    service mpd start
else
    echo "MPD is running"
fi

# prepare database
echo "run composer migrate"
composer migrate

echo ""
echo "########### INITIALIZING FINISHED ###########"
echo ""


if [ "$1" = "test" ]
then
  # apache is necessary for ImageUploadTest
  service apache2 start
  echo "*** Running tests ***"
  composer fixtures
  composer test
  # make sure to exit with test result
  exit $?
else
  echo "*** Starting Webserver ***"
  set -- /usr/local/bin/docker-php-entrypoint "$@"
fi

exec "$@"
