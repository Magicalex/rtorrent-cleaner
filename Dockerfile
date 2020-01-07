FROM alpine:3.11

LABEL description="rtorrent-cleaner is a tool to clean up unnecessary files in rtorrent" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ENV PHP_MEMORY_LIMIT=128M PHP_TIMEZONE=UTC

RUN apk add --no-progress --no-cache \
    php7 \
    php7-phar \
    php7-mbstring \
    php7-xmlrpc \
    php7-json \
    php7-iconv \
  && sed -i 's/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/' /etc/php7/php.ini \
  && sed -i 's/;date.timezone =/date.timezone = ${PHP_TIMEZONE}/' /etc/php7/php.ini \
  && wget https://github.com/Magicalex/rtorrent-cleaner/raw/0.9.6/rtorrent-cleaner-php7.phar \
  && mv --force rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner \
  && chmod +x /usr/local/bin/rtorrent-cleaner

ENTRYPOINT ["rtorrent-cleaner"]
