# rtorrent-cleaner

Script in php for remove unnecessary file in rtorrent.  
Docker image: [docker-rtorrent-cleaner](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)

[![StyleCI](https://github.styleci.io/repos/158750704/shield?branch=master)](https://github.styleci.io/repos/158750704)
[![Latest Stable Version](https://poser.pugx.org/magicalex/rtorrent-cleaner/v/stable)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![Total Downloads](https://poser.pugx.org/magicalex/rtorrent-cleaner/downloads)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![License](https://poser.pugx.org/magicalex/rtorrent-cleaner/license)](https://packagist.org/packages/magicalex/rtorrent-cleaner)

## Requirements

- php 5.6 and above with extension `php-xmlreader` and `php-xmlrpc`

## Installation

### Install php

Example for debian 9
```sh
apt install php7.0-fpm php7.0 php7.0-xml php7.0-xmlrpc
```

### Installation via phar file (recommended)

rtorrent cleaner for php 7.1.3 and above
```sh
wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/0.3.0/rtorrent-cleaner-php7.phar
mv -f rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner
chmod +x /usr/local/bin/rtorrent-cleaner
```

rtorrent cleaner for php 5.6 and above
```sh
wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/0.3.0/rtorrent-cleaner-php5.phar
mv -f rtorrent-cleaner-php5.phar /usr/local/bin/rtorrent-cleaner
chmod +x /usr/local/bin/rtorrent-cleaner
```

### Installation via composer

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

Displaying help:
```sh
rtorrent-cleaner
```

Command `report` for create a report on unnecessary files and missing files:
```sh
rtorrent-cleaner report --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
```

Command `rm` for delete unnecessary files in your download folder:
```sh
rtorrent-cleaner rm --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
# delete without confirmation --assume-yes
rtorrent-cleaner rm --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents --assume-yes
```

Command `mv` for move unnecessary files in a specified folder (here: /home/user/old) :
```sh
rtorrent-cleaner mv /home/user/old/ --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
# move without confirmation --assume-yes
rtorrent-cleaner mv /home/user/old/ --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents --assume-yes
```

Command `torrents` for delete torrents or redownload the missing files:
```sh
rtorrent-cleaner torrents --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
```

Option for the command `mv` `rm` and `report` to ignore files: `--exclude=`
```sh
# php 5.6 and above
rtorrent-cleaner report --exclude=*.sub --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
# php 7.1 and above
rtorrent-cleaner report --exclude=*.sub,*.srt --url-xmlrpc=http://localhost:80/RPC --home=/home/user/torrents
```
This example exclude all files `.sub` and `.srt` in the output
You can add multiple patterns by separating them by `,` only for php 7.1 and above

## Improve performance

Add this [nginx.conf](https://github.com/Magicalex/rtorrent-cleaner/blob/master/nginx.conf) in your nginx configuration.
Adapt your scgi address `scgi_pass 127.0.0.1:5000;`
Check your nginx configuration and restart nginx.

Now, you can use `--url-xmlrpc=http://127.0.0.1:8888` scgi mount point.

## Build Phar rtorrent-cleaner.phar

To build the archive phar, php7.1 and above is required.
```sh
git clone https://github.com/Magicalex/rtorrent-cleaner.git
cd rtorrent-cleaner
composer run-script build-phar-php5
composer run-script build-phar-php7
```

## License

rtorrent-cleaner is released under the MIT License.
