FROM php:8.3-fpm-alpine3.19 AS base

COPY --from=composer:2.6.6 /usr/bin/composer /usr/bin/composer

RUN --mount=type=bind,from=mlocati/php-extension-installer:2.1.75,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions intl zip bcmath apcu opcache pgsql pdo_pgsql

RUN apk add --no-cache \
        bash \
        ca-certificates \
        git \
        su-exec \
        nginx \
        bash-completion \
        openssh \
        openssh-keygen \
        curl

# Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash && \
    apk add --no-cache symfony-cli

# Sécurité PHP
RUN wget -O /usr/local/bin/local-php-security-checker https://github.com/fabpot/local-php-security-checker/releases/download/v2.0.6/local-php-security-checker_2.0.6_linux_amd64 && \
    chmod 755 /usr/local/bin/local-php-security-checker

# Création utilisateur
RUN addgroup bar && \
    adduser -D -h /home -s /bin/bash -G bar foo

# Configuration Nginx & PHP
COPY conf/php.ini /usr/local/etc/php/php.ini
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Copie du script d'entrée
ADD entrypoint.sh /entrypoint
RUN chmod +x /entrypoint

# Création du dossier pour les logs
RUN mkdir -p /run/nginx /var/log/nginx && \
    touch /var/log/nginx/access.log /var/log/nginx/error.log

WORKDIR /srv

# Exposer les ports nécessaires
EXPOSE 80 443

ENV DOMAIN_NAME="default:twittix.local"

# Lancer php-fpm + nginx
ENTRYPOINT ["/entrypoint"]
