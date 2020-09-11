
FROM php:7.4-alpine

LABEL maintainer='Malik Naik <maliknaik16@gmail.com>'

COPY . /app

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN composer install

EXPOSE 8888

CMD ["php", "/app/examples/multiple-things.php"]
