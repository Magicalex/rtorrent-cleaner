<?php

namespace Rtcleaner;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Cleaner
{
    protected $output;
    protected $exclude;
    protected $rtorrent;
    protected $directories;
    protected $numTorrents;
    protected $localFileData;
    protected $missingFileData;
    protected $rtorrentFileData;

    public function __construct($scgi, $port, $excludeFiles, $excludeDirectories, OutputInterface $output)
    {
        $this->output = $output;
        $this->excludeFiles = $excludeFiles;
        $this->excludeDirectories = $excludeDirectories;
        $this->rtorrent = new Rtorrent($scgi, $port);
        $this->getFileListFromRtorrent()->getFileListFromDisk();
    }

    protected function getFileListFromRtorrent()
    {
        $torrents = $this->rtorrent->call('d.multicall2', ['', 'default', 'd.hash=', 'd.name=', 'd.directory=']);
        $this->numTorrents = count($torrents);

        if ($this->numTorrents === 0) {
            throw new \Exception('There is no torrent in rtorrent.');
        }

        $this->missingFileData = [];

        $progressBar = new ProgressBar($this->output, $this->numTorrents);
        $progressBar->setFormat(PHP_EOL.' %bar% %percent%%'.PHP_EOL.' remaining time: %remaining%'.PHP_EOL.' status: %status%'.PHP_EOL);
        $progressBar->setMessage('recovering the files list from rtorrent...', 'status');
        $progressBar->setBarCharacter('<fg=green>█</>');
        $progressBar->setEmptyBarCharacter('█');
        $progressBar->setProgressCharacter('<fg=green>█</>');
        $progressBar->start();

        foreach ($torrents as $torrent) {
            if (is_dir($torrent[2])) {
                $this->directories[] = $torrent[2];
            }

            $files = $this->rtorrent->call('f.multicall', [$torrent[0], '', 'f.path=', 'f.size_bytes=']);

            foreach ($files as $file) {
                if (is_file($torrent[2].'/'.$file[0])) {
                    $this->rtorrentFileData[] = [
                        'absolute_path' => $torrent[2].'/'.$file[0],
                        'size'          => (int) $file[1]
                    ];
                } else {
                    if (array_key_exists($torrent[0], $this->missingFileData)) {
                        $this->missingFileData[$torrent[0]]['files'][] = [
                            'name' => $file[0],
                            'size' => (int) $file[1]
                        ];
                    } else {
                        $this->missingFileData[$torrent[0]] = [
                            'hash'     => $torrent[0],
                            'torrent'  => $torrent[1],
                            'files'    => [
                                ['name' => $file[0], 'size' => (int) $file[1]]
                            ]
                        ];
                    }
                }
            }

            $progressBar->advance(1);
        }

        $this->directories = Helpers::getParentFolder($this->directories);
        $progressBar->setMessage('<fg=green>completed successfully!</>', 'status');
        $progressBar->finish();

        return $this;
    }

    protected function getFileListFromDisk()
    {
        if (count($this->directories) === 0) {
            throw new \Exception('The files are not able to be reached locally.');
        }

        $this->localFileData = [];
        $finder = (new Finder())->in($this->directories)->ignoreDotFiles(false)->files();

        if ($this->excludeDirectories !== null) {
            foreach ($this->excludeDirectories as $dir) {
                $finder->exclude($dir);
            }
        }

        if ($this->excludeFiles !== null) {
            foreach ($this->excludeFiles as $file) {
                $finder->notName($file);
            }
        }

        foreach ($finder as $file) {
            $this->localFileData[] = [
                'absolute_path' => $file->getPathname(),
                'size'          => $file->getSize()
            ];
        }

        return $this;
    }

    public function getFreeDiskSpace()
    {
        return disk_free_space($this->directories[0]);
    }

    public function getnumTorrents()
    {
        return $this->numTorrents;
    }

    public function getMissingFiles()
    {
        return $this->missingFileData;
    }

    public function getFilesNotTracked()
    {
        $output = [];
        $array_local = array_column($this->localFileData, 'absolute_path');
        $array_rtorrent = array_column($this->rtorrentFileData, 'absolute_path');
        $diff = array_diff($array_local, $array_rtorrent);

        foreach ($diff as $absolute_path) {
            foreach ($this->localFileData as $value_local) {
                if ($absolute_path === $value_local['absolute_path']) {
                    $output[] = $value_local;
                    break;
                }
            }
        }

        return $output;
    }

    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = (new Finder())->in($this->directories)->directories();

        foreach ($finder as $dir) {
            $isEmpty = true;
            $handle = opendir($dir->getPathname());

            while (($entry = readdir($handle)) !== false) {
                if ($entry !== '.' && $entry !== '..') {
                    $isEmpty = false;
                    break;
                }
            }

            closedir($handle);

            if ($isEmpty) {
                $emptyDirectory[] = $dir->getPathname();
            }
        }

        return $emptyDirectory;
    }

    public function removeDirectory($emptyDirectory)
    {
        foreach ($emptyDirectory as $dir) {
            rmdir($dir);
            $list[] = Helpers::truncate($dir);
        }

        return $list;
    }

    public function deleteTorrent($hash)
    {
        $this->rtorrent->call('d.erase', [$hash]);
    }

    public function redownload($hash)
    {
        $this->rtorrent->call('d.stop', [$hash]);
        $this->rtorrent->call('d.close', [$hash]);
        $this->rtorrent->call('f.multicall', [$hash, '', 'f.set_create_queued=0', 'f.set_resize_queued=0']);
        $this->rtorrent->call('d.check_hash', [$hash]);
    }
}
