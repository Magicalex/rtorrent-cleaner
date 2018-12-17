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

    public function __construct($home, $urlXmlRpc)
    {
        $this->home = $home;
        $this->urlXmlRpc = $urlXmlRpc;
    }

    public function listingFromRtorrent(OutputInterface $output)
    {
        $rtorrent = new Client($this->urlXmlRpc);

        // call to rtorrent
        $d_param = ['', 'default', 'd.hash=', 'd.name=', 'd.directory='];
        $torrents = $rtorrent->call('d.multicall2', $d_param);

        // init progress bar
        $progressBar = new ProgressBar($output, count($torrents));
        $progressBar->start();
        $currentTorrent = 0;

        foreach ($torrents as $torrent) {
            $basePath = $torrent[2];
            $currentTorrent++;
            $progressBar->advance(1);
            $torrentInfo[$currentTorrent] = ['name' => $torrent[1]];

            // call to rtorrent
            $f_param = [$torrent[0], '', 'f.path=', 'f.size_bytes='];
            $files = $rtorrent->call('f.multicall', $f_param);
            $f_id = 0;

            foreach ($files as $file) {
                $filename = $file[0];
                $size = Str::convertFileSize($file[1], 2);

                $torrentInfo[$currentTorrent]['files']["f{$f_id}"] = [
                    'name' => $filename,
                    'size' => $size
                ];

                $fullPath = "{$basePath}/{$filename}";
                $torrentFile[] = $fullPath;
                $f_id++;
            }
        }

        $progressBar->finish();
        $output->writeln([' <fg=green>Completed!</>', '']);

        return [
            'path' => $torrentFile,
            'info' => $torrentInfo
        ];
    }

    public function listingFromHome($exclude = [])
    {
        $finder = new Finder();
        $finder->in($this->home)->files()->notName($exclude);

        foreach ($finder as $file) {
            $fileTorrentHome[] = $file->getPathname();
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
            $output->writeln(" -> empty directory: <fg=red>{$trunc}</> has been removed");
        }
    }
}
