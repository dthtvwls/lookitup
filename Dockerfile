FROM php:apache

WORKDIR /var/www/html/

COPY index.* ./
COPY composer.* ./

RUN apt-get update \
  && apt-get install -y unzip \
  && ./composer.phar install \
  && rm ./composer.phar
