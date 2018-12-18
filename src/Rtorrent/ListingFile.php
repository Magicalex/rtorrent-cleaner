<?php

namespace RtorrentCleaner\Rtorrent;

use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ListingFile extends Connect
{
    public function listingFromRtorrent(OutputInterface $output)
    {
        $rtorrent = $this->rtorrent();

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
            $torrentInfo[$currentTorrent] = ['name' => $torrent[1], 'hash' => $torrent[0]];

            // call to rtorrent
            $f_param = [$torrent[0], '', 'f.path=', 'f.size_bytes='];
            $files = $rtorrent->call('f.multicall', $f_param);
            $f_id = 0;

            foreach ($files as $file) {
                $fullPath = "{$basePath}/{$file[0]}";
                $size = Str::convertFileSize($file[1], 2);
                $torrentInfo[$currentTorrent]['files']["f{$f_id}"] = [
                    'name' => $fullPath,
                    'size' => $size
                ];
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
}
