FROM php:7.2-fpm

WORKDIR /var/www/golb

RUN apt-get update && apt-get install -y \
&& docker-php-ext-install pdo pdo_mysql \
&& apt-get install -y git \
&& apt-get install -y zip unzip \
&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& composer --version

# RUN apt-get install -y gnupg

RUN apt-get install -y gnupg build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev

# RUN docker-php-ext-configure gd \
#     && docker-php-ext-install gd

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install yarn -y

# && yarn add @symfony/webpack-encore --dev \
# && yarn encore dev

