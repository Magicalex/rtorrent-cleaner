FROM alpine:3.9

LABEL description="Docker image for remove unnecessary file in rtorrent" \
      tags="latest" \
      maintainer="magicalex <magicalex@mondedie.fr>"

ARG VERSION=master

RUN echo "@community http://dl-cdn.alpinelinux.org/alpine/v3.9/community" >> /etc/apk/repositories \
  && apk add -U php7@community php7-phar@community php7-mbstring@community php7-xmlrpc@community \
  && wget https://github.com/Magicalex/rtorrent-cleaner/raw/${VERSION}/rtorrent-cleaner-php7.phar \
  && mv rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner \
  && chmod +x /usr/local/bin/rtorrent-cleaner \
  && rm -rf /var/cache/apk/*

ENTRYPOINT ["rtorrent-cleaner"]
