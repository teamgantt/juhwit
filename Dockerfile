FROM php:7.4-cli

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git zip unzip

COPY --from=composer /usr/bin/composer /usr/bin/composer
