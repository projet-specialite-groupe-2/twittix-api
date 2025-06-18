#!/bin/sh

# Démarrage de php-fpm (en arrière-plan)
php-fpm -D

# Démarrage de nginx (en avant-plan)
exec nginx -g "daemon off;"