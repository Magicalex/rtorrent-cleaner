<?php

namespace RtorrentCleaner\Utils;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Zend\XmlRpc\Client;

class ListingFile
{
    protected $home;
    protected $urlXmlRpc;

    public function __construct(string $home, string $urlXmlRpc)
    {
        $this->home = $home;
        $this->urlXmlRpc = $urlXmlRpc;
    }

    public function listingFromRtorrent(OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, 100);
        $rtorrent = new Client($this->urlXmlRpc);

        $torrentsHash = $rtorrent->call('download_list');
        $currentTorrent = 0;

        $progressBar->start(); // init progress bar
        $totalTorrents = count($torrentsHash);
        $numberUnitTorrents = $totalTorrents / 100;
        $numberOfTorrentsExpected = $numberUnitTorrents;

        foreach ($torrentsHash as $hash) {
            $torrent = $rtorrent->call('d.name', [$hash]);
            $basePath = $rtorrent->call('d.base_path', [$hash]);
            $numberOfFiles = $rtorrent->call('d.size_files', [$hash]);
            $currentTorrent++;

            if ($currentTorrent >= $numberOfTorrentsExpected) {
                $numberOfTorrentsExpected = $numberOfTorrentsExpected + $numberUnitTorrents;
                $progressBar->advance(1);
            }

            $torrentInfo[$currentTorrent] = [
                'name'     => $torrent,
                'nb_files' => $numberOfFiles
            ];

            for ($f_id = 0; $f_id < $numberOfFiles; $f_id++) {
                $file = $rtorrent->call('f.path', ["{$hash}:f{$f_id}"]);

                // Get realpath
                if ($numberOfFiles > 1) {
                    $fullPath = "{$basePath}/{$file}";
                } elseif ($numberOfFiles == 1) {
                    $fullPath = $basePath;
                }

                // Get file size
                $size = $rtorrent->call('f.size_bytes', ["{$hash}:f{$f_id}"]);
                $size = Str::convertFileSize($size, 2);

                // add info in array
                $torrentInfo[$currentTorrent]['files']["f{$f_id}"] = [
                    'name' => $file,
                    'size' => $size
                ];

                // add file in rtorrentFile array
                $torrentFile[] = $fullPath;
            }
        }

        $progressBar->finish();
        $output->writeln([
            ' <fg=green>Completed!</>',
            '' // empty line
        ]);

        return [
            'path' => $torrentFile,
            'info' => $torrentInfo
        ];
    }

    public function listingFromHome()
    {
        $finder = new Finder();
        $finder->in($this->home)->files();

        foreach ($finder as $file) {
            $fileTorrentHome[] = $file->getRealPath();
        }

        return $fileTorrentHome;
    }

    public function getFilesNotTracked($home, $rtorrent)
    {
        return array_diff($home, $rtorrent);
    }

    public function getFilesMissingFromTorrent($rtorrent, $home)
    {
        return array_diff($rtorrent, $home);
    }

    public function getEmptyDirectory()
    {
        $emptyDirectory = [];
        $finder = new Finder();
        $finder->in($this->home)->directories();

        foreach ($finder as $dir) {
            $isEmpty = true;
            $handle = opendir($dir->getRealPath());
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    $isEmpty = false;
                    break;
                }
            }

            closedir($handle);

            if ($isEmpty === true) {
                $emptyDirectory[] = $dir->getRealPath();
            }
        }

        return $emptyDirectory;
    }

    public function removeEmptyDirectory($emptyDirectory, OutputInterface $output)
    {
        foreach ($emptyDirectory as $dir) {
            rmdir($dir);
            $trunc = Str::truncate($dir);
            $output->writeln("empty directory: <fg=red>{$trunc}</> has been removed");
        }
    }
}
