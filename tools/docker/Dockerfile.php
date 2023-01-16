FROM php:8.1-cli
RUN apt-get update
RUN apt-get -y install zlib1g-dev libpng-dev libjpeg-dev libpng-dev libfreetype6-dev default-mysql-client libzip-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql gd zip
RUN mkdir -p /app
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY ./serv.sh /app
COPY ./cli.sh /app
WORKDIR /app
CMD [ "./serv.sh"]
EXPOSE 8000