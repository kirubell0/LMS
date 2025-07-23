#!/bin/bash

# Install ImageMagick extension
apt-get update
apt-get install -y libmagickwand-dev imagemagick
pecl install imagick
docker-php-ext-enable imagick

# Restart PHP-FPM
service php8.1-fpm restart