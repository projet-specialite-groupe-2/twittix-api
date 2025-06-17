FROM php:8.3-fpm-alpine3.19

COPY --from=composer:2.6.6 /usr/bin/composer /usr/bin/composer

RUN  --mount=type=bind,from=mlocati/php-extension-installer:2.1.75,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
      install-php-extensions intl zip bcmath apcu opcache pgsql pdo_pgsql

RUN apk add --no-cache \
        bash \
        ca-certificates \
        git \
        su-exec && \
    addgroup bar && \
    adduser -D -h /home -s /bin/bash -G bar foo

RUN wget -O /usr/local/bin/local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v2.0.6/local-php-security-checker_2.0.6_linux_amd64 && \
    chmod 755 /usr/local/bin/local-php-security-checker

RUN apk add --no-cache bash-completion

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash && \
    apk add --no-cache symfony-cli

RUN apk add openssh openssh-keygen

ADD entrypoint.sh /entrypoint

COPY conf/php.ini /usr/local/etc/php/php.ini

WORKDIR /srv

ENTRYPOINT ["/entrypoint"]
