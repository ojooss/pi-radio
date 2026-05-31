#!/bin/sh

set -e

echo ""
echo "########## INITIALIZING APPLICATION ##########"
PROJECT_PATH=/var/www/html
cd ${PROJECT_PATH}

git config --global --add safe.directory ${PROJECT_PATH}

# Dev: bind mount overrides image; install all dependencies including dev
if [ "$APP_ENV" = "dev" ]; then
    echo "run composer install"
    composer install
fi

# prepare database directory
mkdir -p ${PROJECT_PATH}/var/database

echo "run composer migrate"
composer migrate

# fix permissions on writable paths (after migrate so SQLite file is included)
if [ "$APP_ENV" = "dev" ]; then
    chown -R 1000:www-data ${PROJECT_PATH}
    chmod -R ug+w ${PROJECT_PATH}/var
    chmod -R g+w ${PROJECT_PATH}/public/logos
else
    chown -R www-data:www-data ${PROJECT_PATH}/var
    chown -R www-data:www-data ${PROJECT_PATH}/public/logos
fi

# start media player daemon
if [ $(service mpd status | grep running | wc -l) -lt "1" ]
then
    echo "MPD not running - going to start"
    service mpd start
else
    echo "MPD is running"
fi

# set default volume
mpc volume 50 || true

echo ""
echo "########### INITIALIZING FINISHED ###########"
echo ""


if [ "$1" = "test" ]
then
  # apache is necessary for ImageUploadTest
  service apache2 start
  echo "*** Running tests ***"
  sudo -u www-data composer test
  # make sure to exit with test result
  exit $?
else
  echo "*** Starting Webserver ***"
  set -- /usr/local/bin/docker-php-entrypoint "$@"
fi

exec "$@"
