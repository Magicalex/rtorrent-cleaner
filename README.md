# rtorrent-cleaner

## Build Phar file

add folder vendor/bin in PATH

```sh
composer global require humbug/box
composer install
composer run-script build-phar
```

## Installation

...

## Usage

Show help
```sh
rtorrent-cleaner -h
```

Show help for command report
```sh
rtorrent-cleaner report -h
```

Show help for command rm
```sh
rtorrent-cleaner rm -h
```

## License

rtorrent-cleaner is released under the MIT License.

## TODO

- ??remove torrent without file?? / maybe stop torrent `d.stop` or `d.erase`?
