FROM alpine:3.12 AS builder

RUN apk add --no-progress --no-cache \
    curl \
    git \
    php7 \
    php7-ctype \
    php7-iconv \
    php7-json \
    php7-mbstring \
    php7-openssl \
    php7-phar \
    php7-simplexml \
    php7-tokenizer \
    php7-xmlrpc \
  && cd /tmp \
  && curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && chmod +x /usr/local/bin/composer \
  && git clone https://github.com/Magicalex/rtorrent-cleaner.git /tmp/rtorrent-cleaner \
  && cd /tmp/rtorrent-cleaner \
  && composer build \
  && mv rtorrent-cleaner-php7.2.phar /usr/local/bin/rtorrent-cleaner

FROM alpine:3.12

LABEL description="rtorrent-cleaner is a tool to clean up unnecessary files in rtorrent" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ENV PHP_MEMORY_LIMIT=128M PHP_TIMEZONE=UTC

COPY --from=builder /usr/local/bin /usr/local/bin

RUN apk add --no-progress --no-cache \
    php7 \
    php7-iconv \
    php7-json \
    php7-mbstring \
    php7-phar \
    php7-xmlrpc \
  && sed -i 's/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/' /etc/php7/php.ini \
  && sed -i 's/;date.timezone =/date.timezone = ${PHP_TIMEZONE}/' /etc/php7/php.ini \
  && chmod +x /usr/local/bin/rtorrent-cleaner

ENTRYPOINT ["rtorrent-cleaner"]
