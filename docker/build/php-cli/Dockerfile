FROM php:7.1-cli

RUN apt-get update && apt-get install git unzip bzip2 curl vim -y

RUN apt-get update && apt-get install -y \
    libcurl4-gnutls-dev \
    libmcrypt-dev \
    libpng12-dev \
    libssl-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install -j$(nproc) \
    iconv \
    mcrypt \
    intl \
    mbstring \
    json \
    mcrypt \
    bcmath \
    pdo_mysql

RUN apt-get clean && rm -r /var/lib/apt/lists/*

CMD ["tail", "-f", "/dev/null"]
