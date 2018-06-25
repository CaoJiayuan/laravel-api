FROM registry.cn-hangzhou.aliyuncs.com/0x01301c74/php-fpm-slim:latest

COPY . /var/www
RUN apk add --update --no-cache nginx gettext
RUN apk add --no-cache --virtual .dev git\
    && cd /var/www \
    php composer.phar config -g repo.packagist composer https://packagist.phpcomposer.com \
    && php composer.phar install --no-progress \
    && php composer.phar dumpautoload -o \
    && rm composer.phar \
    && rm -rf /root/.composer/cache/* \
    && rm -fr /tmp/src  \
    && rm -fr /var/cache/apk/* \
    && apk del .dev

RUN chmod +x /var/www/entry-point.sh
COPY crontab /var/spool/cron/crontabs/root
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./doc.conf /etc/nginx/conf.d/doc.conf
RUN chmod -R 0777 /var/www/storage \
  && chmod -R 0777 /var/www/storage
VOLUME /var/www
ENTRYPOINT  /var/www/entry-point.sh

