# rtorrent-cleaner

rtorrent-cleaner is a tool to clean up unnecessary files in rtorrent

[![styleci](https://github.styleci.io/repos/158750704/shield?branch=master)](https://github.styleci.io/repos/158750704)
[![stable](https://img.shields.io/packagist/v/magicalex/rtorrent-cleaner?color=green&style=flat-square)](https://packagist.org/packages/magicalex/rtorrent-cleaner)
![GitHub All Releases](https://img.shields.io/github/downloads/magicalex/rtorrent-cleaner/total?style=flat-square)
[![license](https://img.shields.io/github/license/magicalex/rtorrent-cleaner?color=green&style=flat-square)](https://github.com/Magicalex/rtorrent-cleaner/blob/master/LICENSE)

[![](https://img.shields.io/docker/cloud/build/magicalex/rtorrent-cleaner)](https://hub.docker.com/r/magicalex/rtorrent-cleaner/builds)
[![](https://img.shields.io/docker/cloud/automated/magicalex/rtorrent-cleaner)](https://hub.docker.com/r/magicalex/rtorrent-cleaner/builds)
[![](https://img.shields.io/docker/pulls/magicalex/rtorrent-cleaner)](https://hub.docker.com/r/magicalex/rtorrent-cleaner)
[![](https://img.shields.io/docker/stars/magicalex/rtorrent-cleaner)](https://hub.docker.com/r/magicalex/rtorrent-cleaner)

## Requirements

- php 5.5.9 and above (php 7.2 recommended)
- php extension: `php-cli`, `php-xmlrpc` and `php-mbstring`

## Installation

### Install rtorrent-cleaner from phar file (recommended)

The preferred method of installation is to use the rtorrent-cleaner PHAR which can be downloaded from the most recent [Github Release](https://github.com/Magicalex/rtorrent-cleaner/releases).

### Install rtorrent-cleaner from composer

Install rtorrent-cleaner in global

```sh
composer global require magicalex/rtorrent-cleaner
```

### Install rtorrent-cleaner from DockerHub

#### Requirements

- docker [install docker](https://docs.docker.com/install)

```sh
docker run -it --rm magicalex/rtorrent-cleaner:latest
```

See the details [here](https://github.com/Magicalex/rtorrent-cleaner#usage-with-docker)

## Usage

Displaying help:

```sh
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
  debug     Debug torrents
  help      Displays help for a command
  list      Lists commands
  mv        Move your unnecessary files in a specified folder
  report    Create a report on unnecessary files and missing files
  rm        Delete your unnecessary files in your download folder
  torrents  Delete torrents or redownload the missing files
```

Command `report` for create a report on unnecessary files and missing files:  
(Configuration in your rtorrent.rc ex: `network.scgi.open_port = localhost:5000`)

```sh
rtorrent-cleaner report localhost:5000
```

Example with a socket (file rtorrent.rc `network.scgi.open_local = /home/user/rpc.socket`)

```sh
rtorrent-cleaner report /home/user/rpc.socket
```

You can log the console output in a file with the option --log (path: ./rtorrent-cleaner.log)  
You can define a path (path: /var/log/rtorrent-cleaner.log)

```sh
rtorrent-cleaner report --log -- 127.0.0.1:5000
rtorrent-cleaner report -l /var/log/rtorrent-cleaner.log 127.0.0.1:5000
rtorrent-cleaner report --log=/var/log/rtorrent-cleaner.log 127.0.0.1:5000
```

Command `rm` for delete unnecessary files in your download folder:

```sh
rtorrent-cleaner rm 127.0.0.1:5000
# delete without confirmation --assume-yes or -y
rtorrent-cleaner rm --assume-yes 127.0.0.1:5000
```

Command `mv` for move unnecessary files in a specified folder (ex: /home/user/old) :

```sh
rtorrent-cleaner mv 127.0.0.1:5000 /home/user/old
# move without confirmation --assume-yes or -y
rtorrent-cleaner mv -y 127.0.0.1:5000 /home/user/old
```

Command `torrents` for delete torrents or redownload the missing files:

```sh
rtorrent-cleaner torrents 127.0.0.1:5000
```

Option for the command `mv`, `rm` and `report` to ignore files: `--exclude-files=`

```sh
rtorrent-cleaner report --exclude-files=*.srt 127.0.0.1:5000
rtorrent-cleaner report -f *.sub -f *.srt 127.0.0.1:5000
```

The second example excludes all files `.sub` and `.srt` in the output

Option for the command `mv`, `rm` and `report` to ignore directories: `--exclude-dirs=`  
The directories must be relative to directory default of rtorrent (`directory.default` in rtorrent.rc)

```sh
rtorrent-cleaner report --exclude-dirs=movies 127.0.0.1:5000
rtorrent-cleaner report -d movies -d series 127.0.0.1:5000
```

The second example excludes the `movies` and `series` directories

## Usage with docker

### Environment variables

| Variable | Description | Type | Default value |
| -------- | ----------- | ---- | ------------- |
| **PHP_MEMORY_LIMIT** | Memory limit directive of php | *optional* | 128M
| **PHP_TIMEZONE** | Timezone directive of php | *optional* | UTC

Info: change `<container_name>` by the name of your container of rtorrent  
Info: change `</home/user/downloads>` by your downloads folder  
Info: change `</data/downloads>` by `directory.default` of rtorrent. See your file rtorrent.rc

Command for displaying help: `rtorrent-cleaner`

```sh
docker run -it --rm \
  -v </home/user/downloads>:</data/downloads> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner
```

If you use your container with a network you can connect rtorrent-cleaner like this:  
Info: change `<network_name>` by your network (you can list all the docker networks `docker network ls`)

```sh
docker run -it --rm \
  -v </home/user/downloads>:</data/downloads> \
  --network <network_name> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner
```

Command for making a report: `rtorrent-cleaner report rtorrent:5000`

```sh
docker run -it --rm \
  -v </home/user/downloads>:</data/downloads> \
  --network <network_name> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner report rtorrent:5000
```

You can increase php memory limit if needed with PHP_MEMORY_LIMIT environment variable.  
By default, the memory limit is 128M.

```sh
docker run -it --rm \
  -e PHP_MEMORY_LIMIT=256M \
  -v </home/user/downloads>:</data/downloads> \
  --network <network_name> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner
```

You can change the timezone with PHP_TIMEZONE environment variable.  
By default, the timezone is UTC.

```sh
docker run -it --rm \
  -e PHP_TIMEZONE=Europe/Paris \
  -v </home/user/downloads>:</data/downloads> \
  --network <network_name> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner
```

You can create a script for run rtorrent-cleaner with Docker

```sh
#!/usr/bin/env sh

docker run -it --rm \
  -v </home/user/downloads>:</data/downloads> \
  --network <network_name> \
  --link <container_name>:rtorrent \
  magicalex/rtorrent-cleaner $*
```

Or if you use a socket with rtorrent `/run/php/.rtorrent.sock`.

```sh
#!/usr/bin/env sh

docker run -it --rm \
  -v </home/user/downloads>:</data/downloads> \
  -v /run/php:/run/php \
  magicalex/rtorrent-cleaner $*
```

```sh
chmod +x /usr/local/bin/rtorrent-cleaner
```

Usage:

```
rtorrent-cleaner report rtorrent:5000
rtorrent-cleaner rm rtorrent:5000
rtorrent-cleaner torrents rtorrent:5000
rtorrent-cleaner mv rtorrent:5000 /home/user/old
```

Or with a socket

```
rtorrent-cleaner report /run/php/.rtorrent.sock
rtorrent-cleaner rm /run/php/.rtorrent.sock
rtorrent-cleaner torrents /run/php/.rtorrent.sock
rtorrent-cleaner mv /run/php/.rtorrent.sock /home/user/old
```

### Example with the docker image [linuxserver/rutorrent](https://hub.docker.com/r/linuxserver/rutorrent)

Configure your `docker-compose.yml`

```yml
version: "3"

services:
  rutorrent:
    image: linuxserver/rutorrent:latest
    container_name: rutorrent
    environment:
      - PUID=1000
      - PGID=1000
    volumes:
      - /path/to/rutorrent/config:/config
      - /path/to/rutorrent/downloads:/downloads
      - /run/php:/run/php
    ports:
      - 80:80
      - 51413:51413
      - 6881:6881/udp
    restart: unless-stopped
```

Run linuxserver/rutorrent

```sh
docker-compose up -d
```

Create your rtorrent-cleaner script in `/usr/local/bin` folder

```sh
#!/usr/bin/env sh

docker run -it --rm \
  -e PHP_MEMORY_LIMIT=128M \
  -e PHP_TIMEZONE=Europe/Paris \
  -v /path/to/rutorrent/downloads:/downloads \
  -v /run/php:/run/php \
  magicalex/rtorrent-cleaner $*
```

After this step you can run rtorrent-cleaner

```sh
chmod +x /usr/local/bin/rtorrent-cleaner
rtorrent-cleaner report /run/php/.rtorrent.sock
```

## Build docker image

```sh
docker build -t magicalex/rtorrent-cleaner:latest https://github.com/Magicalex/rtorrent-cleaner.git
```

## Build Phar Archive (rtorrent-cleaner.phar)

To build the archive phar, php 7.2 and `php-phar` extension is required.

```sh
git clone https://github.com/Magicalex/rtorrent-cleaner.git
cd rtorrent-cleaner
composer build
```

## License

rtorrent-cleaner is released under the [MIT License](https://github.com/Magicalex/rtorrent-cleaner/blob/master/LICENSE).
