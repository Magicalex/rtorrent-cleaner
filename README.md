# rtorrent-cleaner

Script in php for remove unnecessary file in rtorrent.  
[![StyleCI](https://github.styleci.io/repos/158750704/shield?branch=master)](https://github.styleci.io/repos/158750704)
[![Latest Stable Version](https://poser.pugx.org/magicalex/rtorrent-cleaner/v/stable)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
[![License](https://poser.pugx.org/magicalex/rtorrent-cleaner/license)](https://packagist.org/packages/magicalex/rtorrent-cleaner)

Docker image: [docker-rtorrent-cleaner](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)  
[![](https://images.microbadger.com/badges/image/magicalex/docker-rtorrent-cleaner.svg)](https://microbadger.com/images/magicalex/docker-rtorrent-cleaner)
[![](https://img.shields.io/docker/automated/magicalex/docker-rtorrent-cleaner.svg)](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner/builds)
[![](https://img.shields.io/docker/pulls/magicalex/docker-rtorrent-cleaner.svg)](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)
[![](https://img.shields.io/docker/stars/magicalex/docker-rtorrent-cleaner.svg)](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)

## Requirements

- php 5.6 and above with extension `php-xmlreader` and `php-xmlrpc`

## Installation

### Install php

Example for debian 9
```sh
apt install php7.0-cli php7.0-xml php7.0-xmlrpc
```

### Install rtorrent-cleaner via phar file (recommended)

See the instructions on releases notes: https://github.com/Magicalex/rtorrent-cleaner/releases

### Install rtorrent-cleaner via composer

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

### Install rtorrent-cleaner via Docker

#### Requirements

- docker [install docker](https://docs.docker.com/install/)

Install docker-rtorrent-cleaner

```sh
docker run -it --rm \
  -v </home/user/torrents>:/data/torrents \
  --network <name_of_network> \
  --link <rtorrent-rutorrent>:rtorrent \
  magicalex/docker-rtorrent-cleaner rtorrent-cleaner
```

See the details [here](https://github.com/Magicalex/rtorrent-cleaner#usage-with-docker)

## Usage

Displaying help:
```sh
$ rtorrent-cleaner
      _                            _          _
 _ __| |_ ___  _ __ _ __ ___ _ __ | |_    ___| | ___  __ _ _ __   ___ _ __
| '__| __/ _ \| '__| '__/ _ \ '_ \| __|  / __| |/ _ \/ _` | '_ \ / _ \ '__|
| |  | || (_) | |  | | |  __/ | | | |_  | (__| |  __/ (_| | | | |  __/ |
|_|   \__\___/|_|  |_|  \___|_| |_|\__|  \___|_|\___|\__,_|_| |_|\___|_|
rtorrent-cleaner version x.x.x

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
# delete without confirmation --assume-yes or -y
$ rtorrent-cleaner rm --url-xmlrpc=http://localhost:80/RPC -y
```

Command `mv` for move unnecessary files in a specified folder (ex: /home/user/old) :
```sh
$ rtorrent-cleaner mv /home/user/old --url-xmlrpc=http://localhost:80/RPC
# move without confirmation --assume-yes or -y
$ rtorrent-cleaner mv /home/user/old --url-xmlrpc=http://localhost:80/RPC -y
```

Command `torrents` for delete torrents or redownload the missing files:
```sh
$ rtorrent-cleaner torrents --url-xmlrpc=http://localhost:80/RPC
```

Option for the command `mv`, `rm` and `report` to ignore files: `--exclude=`
```sh
$ rtorrent-cleaner report --exclude=*.sub --url-xmlrpc=http://localhost:80/RPC
$ rtorrent-cleaner report -e *.sub -e *.srt --url-xmlrpc=http://localhost:80/RPC
```
The second example excludes all files `.sub` and `.srt` in the output

Option for a Basic authentication `--username` and `--password` for the command `report`, `rm`, `mv` and `torrents`
```sh
$ rtorrent-cleaner report --url-xmlrpc=https://domain.tld/RPC --username=john --password=azerty
```

## Improve performance

Add this [nginx.conf](https://github.com/Magicalex/rtorrent-cleaner/blob/master/nginx.conf) in your nginx configuration.
Adapt your scgi address `scgi_pass 127.0.0.1:5000;`
Check your nginx configuration and restart nginx.

Now, you can use `--url-xmlrpc=http://127.0.0.1:8888` scgi mount point.

## Usage with docker

Info: change `<rtorrent-rutorrent>` by the name of your container of rtorrent here: rtorrent-rutorrent  
Info: change `</home/user/torrents>` by your torrents folder

Command for displaying help: `rtorrent-cleaner`
```sh
docker run -it --rm \
  -v </home/user/torrents>:/data/torrents \
  --link <rtorrent-rutorrent>:rtorrent \
  magicalex/docker-rtorrent-cleaner rtorrent-cleaner
```

If you use your container with a network you can connect rtorrent-cleaner like this:  
Info: change `<name_of_network>` by your network (you can list all the docker networks `docker network ls`)
```sh
docker run -it --rm \
  -v </home/user/torrents>:/data/torrents \
  --network <name_of_network> \
  --link <rtorrent-rutorrent>:rtorrent \
  magicalex/docker-rtorrent-cleaner rtorrent-cleaner
```

Command for making a report: `rtorrent-cleaner report --url-xmlrpc=http://rtorrent:8080/RPC`
```sh
docker run -it --rm \
  -v </home/user/torrents>:/data/torrents \
  --network <name_of_network> \
  --link <rtorrent-rutorrent>:rtorrent \
  magicalex/docker-rtorrent-cleaner rtorrent-cleaner report --url-xmlrpc=http://rtorrent:8080/RPC
```

## Build a php archive Phar (rtorrent-cleaner.phar)

To build the archive phar, php7.1 and above is required.
```sh
git clone https://github.com/Magicalex/rtorrent-cleaner.git
cd rtorrent-cleaner
composer run-script build-phar-php5
composer run-script build-phar-php7
```

## Build docker image

```sh
docker build -t magicalex/docker-rtorrent-cleaner:latest https://github.com/Magicalex/rtorrent-cleaner.git#master:docker-rtorrent-cleaner
```

## License

rtorrent-cleaner is released under the [MIT License](https://github.com/Magicalex/rtorrent-cleaner/blob/master/LICENSE).
