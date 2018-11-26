<?php

namespace RtorrentCleaner\Utils;

use ByteUnits\Binary;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Zend\XmlRpc\Client;

class ListingFile
{
    protected $home;
    protected $urlXmlrcp;

    public function __construct(string $home, string $urlXmlrcp)
    {
        $this->home = $home;
        $this->urlXmlrcp = $urlXmlrcp;
    }

    public function listing_from_rtorrent(OutputInterface $output)
    {
        $progress_bar = new ProgressBar($output, 100);
        $rtorrent = new Client($this->urlXmlrcp);

        $hash_torrents = $rtorrent->call('download_list');
        $current_torrent = 0;

        $progress_bar->start(); // init progress bar
        $total_torrents = count($hash_torrents);
        $number_unit_torrents = $total_torrents / 100;
        $number_of_torrents_expected = $number_unit_torrents;

        foreach ($hash_torrents as $hash) {
            $torrent = $rtorrent->call('d.name', [$hash]);
            $number_of_files = $rtorrent->call('d.size_files', [$hash]);
            $current_torrent++;

            if ($current_torrent >= $number_of_torrents_expected) {
                $number_of_torrents_expected = $number_of_torrents_expected + $number_unit_torrents;
                $progress_bar->advance(1);
            }

            $torrentInfo[$current_torrent] = [
                'name'     => $torrent,
                'nb_files' => $number_of_files
            ];

            for ($f_id = 0; $f_id < $number_of_files; $f_id++) {
                $file = $rtorrent->call('f.path', ["{$hash}:f{$f_id}"]);

                // Get realpath
                if ($number_of_files > 1 || is_dir("{$this->home}/{$torrent}") === true) {
                    $full_path = "{$this->home}/{$torrent}/{$file}";
                } elseif ($number_of_files == 1 && is_file("{$this->home}/{$file}") === true) {
                    $full_path = "{$this->home}/{$file}";
                }

                // Get file size
                $size = $rtorrent->call('f.size_bytes', ["{$hash}:f{$f_id}"]);
                $size = Binary::bytes($size)->format(2);

                // add info in array
                $torrentInfo[$current_torrent]['files']["f{$f_id}"] = [
                    'name' => $file,
                    'size' => $size
                ];

                // add file in rtorrentFile array
                $torrentFile[] = $full_path;
            }
        }

        $progress_bar->finish();
        $output->writeln([
            ' <fg=green>Completed!</>',
            '' // empty line
        ]);

        return [
            'path' => $torrentFile,
            'info' => $torrentInfo
        ];
    }

    public function listing_from_home()
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
