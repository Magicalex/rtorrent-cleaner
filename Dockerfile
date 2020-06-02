FROM alpine:3.12

LABEL description="rtorrent-cleaner is a tool to clean up unnecessary files in rtorrent" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ENV PHP_MEMORY_LIMIT=128M PHP_TIMEZONE=UTC

RUN apk add --no-progress --no-cache \
    php7 \
    php7-iconv \
    php7-json \
    php7-mbstring \
    php7-phar \
    php7-xmlrpc \
  && sed -i 's/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/' /etc/php7/php.ini \
  && sed -i 's/;date.timezone =/date.timezone = ${PHP_TIMEZONE}/' /etc/php7/php.ini \
  && wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/0.9.7/rtorrent-cleaner-php7.2.phar \
  && mv rtorrent-cleaner-php7.2.phar /usr/local/bin/rtorrent-cleaner \
  && chmod +x /usr/local/bin/rtorrent-cleaner

ENTRYPOINT ["rtorrent-cleaner"]
