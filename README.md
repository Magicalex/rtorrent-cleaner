# rtorrent-cleaner

Script in php for remove unnecessary file in rtorrent.  
Docker image: [docker-rtorrent-cleaner](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)

[![StyleCI](https://github.styleci.io/repos/158750704/shield?branch=master)](https://github.styleci.io/repos/158750704)
[![Latest Stable Version](https://poser.pugx.org/magicalex/rtorrent-cleaner/v/stable)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![Total Downloads](https://poser.pugx.org/magicalex/rtorrent-cleaner/downloads)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![License](https://poser.pugx.org/magicalex/rtorrent-cleaner/license)](https://packagist.org/packages/magicalex/rtorrent-cleaner)

## Requirements

- php 7 with extension php-bcmath and php-dom
- composer

## Installation

Install the dependencies for debian 9
```sh
apt-get install php php-bcmath php-dom
```

Install composer for root user
```sh
curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer
mkdir /root/.composer && composer install -d /root/.composer
echo 'export PATH="$PATH:/root/.composer/vendor/bin"' >> /root/.bashrc
source /root/.bashrc
```

Install rtorrent-cleaner in global
```sh
composer global require magicalex/rtorrent-cleaner
```

## Usage

Command for displaying help:
```sh
rtorrent-cleaner
```

Command for making a report:
```sh
rtorrent-cleaner report --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents
```

Command for remove unnecessary files:
```sh
rtorrent-cleaner rm --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents
```

Command for remove unnecessary files without confirmation (--assume-yes):
```sh
rtorrent-cleaner rm --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents --assume-yes
```

## [WIP] Build Phar rtorrent-cleaner.phar

Does not work yet

```sh
composer global require humbug/box
composer install
composer run-script build-phar
```

## License

rtorrent-cleaner is released under the MIT License.

## TODO

- remove torrent without file ? (maybe stop torrent `d.stop` or `d.erase`)
