# rtorrent-cleaner

Script in php for remove unnecessary file in rtorrent.  
Docker image: [docker-rtorrent-cleaner](https://hub.docker.com/r/magicalex/docker-rtorrent-cleaner)

## Installation

Install the dependencies for debian 9
```sh
apt-get install php php-bcmath php-dom
```

Install composer
```sh
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer
echo 'export PATH="$PATH:~/.config/composer/vendor/bin"' >> ~/.bashrc
```

Install rtorrent-cleaner in global
```sh
composer global require magicalex/rtorrent-cleaner
```

## Usage

Show help
```sh
rtorrent-cleaner -h
```

Show help for command `report`
```sh
rtorrent-cleaner report -h
```

Show help for command `rm`
```sh
rtorrent-cleaner rm -h
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
