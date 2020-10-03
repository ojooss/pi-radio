#!/bin/sh

set -e

echo "#########################################"
PROJECT_PATH=/var/www/html/

# install dependencies
if [ ! -f ${PROJECT_PATH}composer.done -o ${PROJECT_PATH}composer.lock -nt ${PROJECT_PATH}composer.done ]; then
##composer install --no-dev
  composer install
  composer migrate
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

echo "#########################################"


if [ "$1" = "test" ]
then
  # apache is necessary for ImageUploadTest
  service apache2 start
  echo "*** Running tests ***"
  composer test
  # make sure to exit with test result
  exit $?
else
  echo "*** Starting Webserver ***"
  set -- /usr/local/bin/docker-php-entrypoint "$@"
fi

exec "$@"
