<?php

namespace Rtorrent\Cleaner;

use Rtorrent\Cleaner\Rtorrent;
use Rtorrent\Cleaner\Helpers;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Cleaner
{
    protected $output;
    protected $exclude;
    protected $rtorrent;
    protected $numTorrents;
    protected $rtorrentData;
    protected $localFileData;
    protected $rtorrentFileData;
    protected $directories = [];

    public function __construct($scgi, $port, $exclude = null, OutputInterface $output)
    {
        $this->output = $output;
        $this->exclude = $exclude;
        $this->rtorrent = new Rtorrent($scgi, $port);
        $this->getFileListFromRtorrent()->getFileListFromDisk();
    }

    protected function getFileListFromRtorrent()
    {
        try {
            $torrents = $this->rtorrent->call('d.multicall2', ['', 'default', 'd.hash=', 'd.name=', 'd.directory=']);
            $this->numTorrents = count($torrents);
            if ($this->numTorrents == 0) {
                throw new \Exception('There is no torrent in rtorrent.');
            }
        } catch (\Exception $error) {
            Helpers::errorMessage($error->getMessage(), $this->output);
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
            if (is_dir($torrent[2]) === true) {
                $this->directories[] = $torrent[2];
            }

            $this->rtorrentData[] = ['name' => $torrent[1], 'hash' => $torrent[0]];
            $files = $this->rtorrent->call('f.multicall', [$torrent[0], '', 'f.path=', 'f.size_bytes=']);

            foreach ($files as $file) {
                $fullPath = "{$torrent[2]}/{$file[0]}";
                $this->rtorrentData[$nb]['file'][] = [
                    'full_path' => $fullPath,
                    'size' => $file[1]
                ];

                $this->rtorrentFileData[] = [
                    'full_path' => $fullPath,
                    'size' => $file[1]
                ];
            }

            $progressBar->advance(1);
        }

        $this->directories = array_unique($this->directories);

        $progressBar->setMessage('<fg=green>completed successfully!</>', 'status');
        $progressBar->finish();

        return $this;
    }

    protected function getFileListFromDisk()
    {
        if (count($this->directories) == 0) {
            Helpers::errorMessage('The files are not able to be reached locally.', $this->output);
            exit(1);
        }

        $finder = new Finder();
        $finder->in($this->directories)->files()->ignoreDotFiles(false);

        if ($this->exclude !== null) {
            foreach ($this->exclude as $pattern) {
                $finder->notName($pattern);
            }
        }

        $i = 0;
        foreach ($finder as $file) {
            $this->localFileData[$i]['full_path'] = $file->getPathname();
            $this->localFileData[$i]['size'] = $file->getSize();
            $i++;
        }

        $this->localFileData = array_intersect_key(
            $this->localFileData,
            array_unique(array_map('serialize', $this->localFileData))
        );

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
        return Helpers::array_diff($this->localFileData, $this->rtorrentFileData);
    }

    public function getFilesMissingFromRtorrent()
    {
        return Helpers::array_diff($this->rtorrentFileData, $this->localFileData);
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
                if ($entry != '.' && $entry != '..') {
                    $isEmpty = false;
                    break;
                }
            }

            closedir($handle);

            if ($isEmpty === true) {
                $emptyDirectory[] = $dir->getPathname();
            }
        }

        return array_unique($emptyDirectory);
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
        $this->call('d.erase', [$hash]);
    }

    public function redownload($hash)
    {
        $this->call('d.stop', [$hash]);
        $this->call('d.close', [$hash]);
        $this->call('f.multicall', [$hash, '', 'f.set_create_queued=0', 'f.set_resize_queued=0']);
        $this->call('d.check_hash', [$hash]);
        $this->call('d.open', [$hash]);
        $this->call('d.start', [$hash]);
    }

    protected function findTorrentHash($data, $missingFile)
    {
        foreach ($data as $torrent) {
            foreach ($torrent['files'] as $file) {
                if ($file['name'] == $missingFile) {
                    $hash = $torrent['hash'];
                    $name = $torrent['name'];
                    break;
                }
            }
        }

        return ['hash' => $hash, 'name' => $name];
    }

    public function listTorrentMissingFile($missingFile, $dataRtorrent)
    {
        $torrentMissingFile = [];
        $findHash = false;

        foreach ($missingFile as $file) {
            $torrent = $this->findTorrentHash($dataRtorrent['data-torrent'], $file);

            // check if $hash has been already add
            foreach ($torrentMissingFile as $id => $info) {
                if ($info['hash'] == $torrent['hash']) {
                    $torrentMissingFile[$id]['files'][] = $file;
                    $findHash = true;
                    break;
                }
            }

            if ($findHash === false) {
                $torrentMissingFile[] = [
                    'hash'  => $torrent['hash'],
                    'name'  => $torrent['name'],
                    'files' => [$file]
                ];
            }

            $findHash = false;
        }

        return $torrentMissingFile;
    }
}
