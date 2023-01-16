FROM php:8.1-cli
RUN apt-get update
RUN apt-get -y install zlib1g-dev libpng-dev libjpeg-dev libpng-dev libfreetype6-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql gd
COPY . /app
WORKDIR /app
CMD [ "./serv"]
EXPOSE 8000