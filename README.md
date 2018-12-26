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

See the instructions on the releases: https://github.com/Magicalex/rtorrent-cleaner/releases

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
$ rtorrent-cleaner
rtorrent-cleaner 0.4.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help      Displays help for a command
  list      Lists commands
  mv        Move your unnecessary files in a specified folder
  report    Create a report on unnecessary files and missing files
  rm        Delete your unnecessary files in your download folder
  torrents  Delete torrents or redownload the missing files
```

Command `report` for create a report on unnecessary files and missing files:
```sh
$ rtorrent-cleaner report --url-xmlrpc=http://localhost:80/RPC
# you can log the console output in a file with the option --log (rtorrent-cleaner.log)
$ rtorrent-cleaner report --log --url-xmlrpc=http://localhost:80/RPC
# you can define a path (ex: /var/log/rtorrent-cleaner.log)
$ rtorrent-cleaner report --log=/var/log/rtorrent-cleaner.log --url-xmlrpc=http://localhost:80/RPC
```

Command `rm` for delete unnecessary files in your download folder:
```sh
$ rtorrent-cleaner rm --url-xmlrpc=http://localhost:80/RPC
# delete without confirmation --assume-yes
$ rtorrent-cleaner rm --url-xmlrpc=http://localhost:80/RPC --assume-yes
```

Command `mv` for move unnecessary files in a specified folder (ex: /home/user/old) :
```sh
$ rtorrent-cleaner mv /home/user/old --url-xmlrpc=http://localhost:80/RPC
# move without confirmation --assume-yes
$ rtorrent-cleaner mv /home/user/old --url-xmlrpc=http://localhost:80/RPC --assume-yes
```

Command `torrents` for delete torrents or redownload the missing files:
```sh
$ rtorrent-cleaner torrents --url-xmlrpc=http://localhost:80/RPC
```

Option for the command `mv`, `rm` and `report` to ignore files: `--exclude=`
```sh
# php 5.6 and above
$ rtorrent-cleaner report --exclude=*.sub --url-xmlrpc=http://localhost:80/RPC
# php 7.1 and above
$ rtorrent-cleaner report --exclude=*.sub,*.srt --url-xmlrpc=http://localhost:80/RPC
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
