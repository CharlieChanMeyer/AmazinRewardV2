From php:8.1.0-fpm

WORKDIR /app

RUN apt-get update

RUN apt-get -y install git zip libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql pgsql

RUN curl -sL https://getcomposer.org/installer | php -- --install-dir /usr/bin --filename composer

RUN pecl install xdebug

CMD ["php-fpm"]

$2y$10$xgG1Tr7VaELBfY43KV1V.uruJsIuxBTWq/W4XH12usFA/KH7.xoRy