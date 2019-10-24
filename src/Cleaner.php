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
    protected $rtorrentData;
    protected $localFileData;
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
        try {
            $torrents = $this->rtorrent->call('d.multicall2', ['', 'default', 'd.hash=', 'd.name=', 'd.directory=']);
            $this->numTorrents = count($torrents);
            if ($this->numTorrents === 0) {
                throw new \Exception('There is no torrent in rtorrent.');
            }
        } catch (\Exception $exception) {
            Helpers::errorMessage($exception->getMessage(), $this->output);
            exit(1);
        }

        $progressBar = new ProgressBar($this->output, $this->numTorrents);
        $progressBar->setFormat(" %bar% %percent%%\n remaining time: %remaining%\n status: %status%\n");
        $progressBar->setMessage('recovering the files list from rtorrent...', 'status');
        $progressBar->setBarCharacter('<fg=green>█</>');
        $progressBar->setEmptyBarCharacter('█');
        $progressBar->setProgressCharacter('<fg=green>█</>');
        $progressBar->start();

        foreach ($torrents as $nb => $torrent) {
            if (is_dir($torrent[2])) {
                $this->directories[] = $torrent[2];
            }

            $this->rtorrentData[] = ['name' => $torrent[1], 'hash' => $torrent[0]];
            $files = $this->rtorrent->call('f.multicall', [$torrent[0], '', 'f.frozen_path=', 'f.size_bytes=']);

            foreach ($files as $file) {
                if (is_file($file[0]) === false) {
                    continue;
                }

                $this->rtorrentData[$nb]['file'][] = [
                    'absolute_path' => $file[0],
                    'size'          => $file[1]
                ];

                $this->rtorrentFileData[] = [
                    'absolute_path' => $file[0],
                    'size'          => $file[1]
                ];
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
            Helpers::errorMessage('The files are not able to be reached locally.', $this->output);
            exit(1);
        }

        $finder = new Finder();
        $finder->in($this->directories)->files()->ignoreDotFiles(false);
        $this->localFileData = [];

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

    public function getFilesNotTracked()
    {
        return Helpers::find_diff($this->localFileData, $this->rtorrentFileData);
    }

    public function getFilesMissingFromRtorrent()
    {
        return Helpers::find_diff($this->rtorrentFileData, $this->localFileData);
    }

    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = new Finder();
        $finder->in($this->directories)->directories();

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
        $this->rtorrent->call('d.open', [$hash]);
        $this->rtorrent->call('d.start', [$hash]);
    }

    protected function findTorrentHash($missingFile)
    {
        foreach ($this->rtorrentData as $torrent) {
            foreach ($torrent['file'] as $file) {
                if ($file['absolute_path'] === $missingFile) {
                    $hash = $torrent['hash'];
                    $name = $torrent['name'];
                    break;
                }
            }
        }

        return ['hash' => $hash, 'name' => $name];
    }

    public function getTorrentsMissingFile()
    {
        $missingFile = $this->getFilesMissingFromRtorrent();
        $nbMissingFile = count($missingFile);
        $findHash = false;
        $output = [];

        foreach ($missingFile as $file) {
            $torrent = $this->findTorrentHash($file['absolute_path']);

            foreach ($output as $id => $info) {
                if ($info['hash'] === $torrent['hash']) {
                    $output[$id]['file'][] = $file;
                    $findHash = true;
                    break;
                }
            }

            if (!$findHash) {
                $output[] = [
                    'hash' => $torrent['hash'],
                    'name' => $torrent['name'],
                    'file' => [$file]
                ];
            }

            $findHash = false;
        }

        return ['data' => $output, 'nb' => $nbMissingFile];
    }
}
