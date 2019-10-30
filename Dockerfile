FROM alpine:3.10

LABEL description="Docker image for remove unnecessary file in rtorrent" \
      tags="latest" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ENV PHP_MEMORY_LIMIT=128M

COPY rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner

RUN echo "@community http://dl-cdn.alpinelinux.org/alpine/v3.10/community" >> /etc/apk/repositories \
  && apk add -U php7@community php7-phar@community php7-mbstring@community php7-xmlrpc@community php7-json@community php7-iconv@community \
  && sed -i 's/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/' /etc/php7/php.ini \
  && chmod +x /usr/local/bin/rtorrent-cleaner \
  && rm -rf /var/cache/apk/*

ENTRYPOINT ["rtorrent-cleaner"]
