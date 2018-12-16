# rtorrent-cleaner

Script in php for remove unnecessary file in rtorrent.  
Docker image: [docker-rtorrent-cleaner](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)

[![StyleCI](https://github.styleci.io/repos/158750704/shield?branch=master)](https://github.styleci.io/repos/158750704)
[![Latest Stable Version](https://poser.pugx.org/magicalex/rtorrent-cleaner/v/stable)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![Total Downloads](https://poser.pugx.org/magicalex/rtorrent-cleaner/downloads)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![License](https://poser.pugx.org/magicalex/rtorrent-cleaner/license)](https://packagist.org/packages/magicalex/rtorrent-cleaner)

## Requirements

- php 5.6 and above with extension php-bcmath and php-dom

## Installation

### Installation via phar file (recommended)

rtorrent cleaner for php 7

```sh
wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/0.2.4/rtorrent-cleaner-php7.phar
mv rtorrent-cleaner-php7.phar /usr/local/bin/rtorrent-cleaner
chmod +x /usr/local/bin/rtorrent-cleaner
```

rtorrent cleaner for php 5

```sh
wget https://github.com/Magicalex/rtorrent-cleaner/releases/download/0.2.4/rtorrent-cleaner-php5.phar
mv rtorrent-cleaner-php5.phar /usr/local/bin/rtorrent-cleaner
chmod +x /usr/local/bin/rtorrent-cleaner
```

### Installation via composer

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

Command for move unnecessary files in a folder (here: /home/user/old) :
```sh
rtorrent-cleaner mv /home/user/old/ --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents
```

Command for move unnecessary files in a folder (here: /home/user/old) without confirmation (--assume-yes):
```sh
rtorrent-cleaner mv /home/user/old/ --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents --assume-yes
```

Option for ignore files (option `--exclude=`) :
```sh
rtorrent-cleaner report --exclude=*.sub,*.srt --url-xmlrpc=http://localhost/RPC --home=/home/user/torrents
```
This example exclude all files `.sub` and `.srt` in the output

## Improve performance

Add this [nginx.conf](https://github.com/Magicalex/rtorrent-cleaner/blob/master/nginx.conf) in your nginx configuration.
Adapt your scgi address `scgi_pass 127.0.0.1:5000;`
Check your nginx configuration and restart nginx.

Now, you can use `--url-xmlrpc=http://127.0.0.1:8888` scgi mount point.

## Build Phar rtorrent-cleaner.phar

```sh
composer global require humbug/box
git clone https://github.com/Magicalex/rtorrent-cleaner.git
cd rtorrent-cleaner
composer run-script build-phar-php5
composer run-script build-phar-php7
```

## License

rtorrent-cleaner is released under the MIT License.

## TODO

- remove torrent without file ? (maybe stop torrent `d.stop` or `d.erase`)
- add log file support
