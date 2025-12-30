FROM php:8.4-apache

# update
RUN apt update

# SW needed
RUN apt install make -y --fix-missing
RUN apt install ccache apt-utils -y --fix-missing
RUN apt install gcc -y --fix-missing

# Opcache
RUN docker-php-ext-configure opcache --enable-opcache
RUN docker-php-ext-install -j "$(nproc)" opcache

# search by ascii
RUN apt install libicu-dev -y --fix-missing
RUN docker-php-ext-install -j "$(nproc)" intl

# Custom Opcache
RUN ( \
  echo "opcache.enable=1"; \
  echo "opcache.jit_buffer_size=256M"; \
  echo "opcache.memory_consumption=128"; \
  echo "opcache.interned_strings_buffer=8"; \
  echo "opcache.max_accelerated_files=20000"; \
  echo "opcache.revalidate_freq=5"; \
  echo "opcache.fast_shutdown=1"; \
  echo "opcache.enable_cli=1"; \
  ) >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# mod rewrite
RUN a2enmod rewrite
RUN service apache2 restart

# set ServerName and prevent some warning
RUN echo "ServerName web-project-web" >> /etc/apache2/apache2.conf

# Installation of Composer
RUN apt install git zip libzip-dev -y --fix-missing
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Custom php.ini
COPY ./.docker/php/config/php.ini /usr/local/etc/php/

WORKDIR /var/www/html