FROM php:7.4-apache


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
RUN docker-php-ext-install pdo pdo_mysql mysqli \
 # php-intl
 && docker-php-ext-configure intl && docker-php-ext-install intl \
# xdebug
 && pecl install xdebug && docker-php-ext-enable xdebug


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


# add and init application
COPY . /var/www/html
RUN bash /var/www/html/docker/entrypoint.sh


# Start image
COPY docker/entrypoint.sh /usr/local/bin/piradio-entrypoint.sh
ENTRYPOINT ["sh", "/usr/local/bin/piradio-entrypoint.sh"]
CMD ["apache2-foreground"]
