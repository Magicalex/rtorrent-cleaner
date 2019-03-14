FROM alpine:3.9

LABEL description="Docker image for remove unnecessary file in rtorrent" \
      tags="latest" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ARG VERSION=0.7.0

RUN echo "@community https://nl.alpinelinux.org/alpine/v3.9/community" >> /etc/apk/repositories \
  && apk add -U \
     php7@community \
     php7-fpm@community \
     php7-json@community \
     php7-phar@community \
     php7-mbstring@community \
     php7-openssl@community \
     php7-dom@community \
     php7-simplexml@community \
     php7-ctype@community \
     php7-xmlreader \
     php7-xmlrpc \
  && cd /tmp \
  && wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/$VERSION/rtorrent-cleaner-php7.phar \
  && mv rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner \
  && chmod +x /usr/local/bin/rtorrent-cleaner \
  && rm -rf /var/cache/apk/*
