FROM ubuntu:latest
ENV DEBIAN_FRONTEND noninteractive
ENV COMPOSER_ALLOW_SUPERUSER 1
# APACHE  Y DEPENDENCIAS UBUNTU
RUN apt-get update && apt-get install -y apache2 curl unzip wget && apt-get clean

# PHP
RUN apt-get install software-properties-common -y && \
    add-apt-repository ppa:ondrej/php -y && \
    apt -qq -y install php8.1

# PHP DEPENDENDENCIAS
RUN apt install -y libapache2-mod-php8.1 \
    php8.1-mysql  \
    php8.1-xml  \
    php8.1-gd  \
    php8.1-mbstring  \
    php8.1-bcmath \
    php8.1-cli \
    php8.1-curl

# COMPOSER
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# NPM
RUN apt install -y nodejs npm

COPY 000-default.conf /etc/apache2/sites-available
WORKDIR /var/www/html/frikyland
COPY . /var/www/html/frikyland

CMD bash -c "composer install && npm install && npm run build && /usr/sbin/apache2ctl -DFOREGROUND"