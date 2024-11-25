# basic image
###################################
FROM php:8.4-apache as base


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
RUN install-php-extensions pdo pdo_mysql mysqli intl xdebug-3.4.0beta1


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
    apt-get clean
COPY docker/mpd.conf /etc/mpd.conf
COPY docker/sudoers.conf /etc/sudoers.d/piradio


# interim image: add and init application
###################################
FROM base as app
COPY . /var/www/html
RUN bash /var/www/html/docker/entrypoint.sh
RUN chown -R www-data:www-data /var/www/html


# main image
###################################
FROM base

# add and init application
COPY --from=app /var/www/html /var/www/html

# Start image
COPY docker/entrypoint.sh /usr/local/bin/piradio-entrypoint.sh
ENTRYPOINT ["sh", "/usr/local/bin/piradio-entrypoint.sh"]
CMD ["apache2-foreground"]
