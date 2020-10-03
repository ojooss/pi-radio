FROM php:7.4-apache


# COMPOSER
COPY --from=composer /usr/bin/composer /usr/bin/composer


# linux packages
RUN apt-get update && \
    apt-get install -y git zip && \
    apt-get clean


# PHP modules
RUN docker-php-ext-install pdo pdo_mysql mysqli
# xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug


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


# add application
COPY . /var/www/html


# Start image
COPY docker/entrypoint.sh /usr/local/bin/piradio-entrypoint.sh
ENTRYPOINT ["sh", "/usr/local/bin/piradio-entrypoint.sh"]
CMD ["apache2-foreground"]
