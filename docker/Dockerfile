FROM php:8.5-apache


# COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer


# linux packages
RUN apt-get update && \
    apt-get install -y git zip && \
    # sudo for app
    apt-get install -y sudo && \
    # libicu for php-intl
    apt-get install -y libicu-dev && \
    apt-get clean


# PHP modules
# see: https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo pdo_mysql mysqli intl xdebug


# apache configuration
RUN a2enmod headers && \
    a2enmod rewrite && \
    a2enmod ssl && \
    a2enmod proxy && \
    a2enmod proxy_http
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf


# install Music-Player-Daemon (MPD), Music-Player-Client (MPC) and
# Advanced Linux Sound Architecture (alsa-utils)
RUN apt-get update && \
    apt-get install -y mpd mpc alsa-utils && \
    apt-get clean && \
    mkdir -p /var/lib/mpd /var/run/mpd && \
    chown -R mpd:audio /var/lib/mpd /var/run/mpd
COPY docker/mpd.conf /etc/mpd.conf
COPY docker/sudoers.conf /etc/sudoers.d/piradio


# Application
COPY . /var/www/html
RUN APP_ENV=prod composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html


# Start image
COPY docker/entrypoint.sh /usr/local/bin/piradio-entrypoint.sh
ENTRYPOINT ["sh", "/usr/local/bin/piradio-entrypoint.sh"]
CMD ["apache2-foreground"]
