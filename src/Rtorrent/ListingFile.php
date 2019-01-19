<?php

namespace Rtorrent\Cleaner\Rtorrent;

use Rtorrent\Cleaner\Utils\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ListingFile extends Connect
{
    public function listingFromRtorrent(OutputInterface $output)
    {
        $d_param = ['', 'default', 'd.hash=', 'd.name=', 'd.directory='];
        $torrents = $this->rtorrent->call('d.multicall2', $d_param);

        // progress bar
        $progressBar = new ProgressBar($output, count($torrents));
        $progressBar->setFormat(" %percent%% %bar% %remaining%\n Status: %status%\n");
        $progressBar->setBarCharacter('<fg=green>█</>');
        $progressBar->setEmptyBarCharacter('█');
        $progressBar->setProgressCharacter('<fg=yellow>█</>');
        $progressBar->start();
        $progressBar->setMessage('<fg=yellow>recovering the files list from rtorrent...</>', 'status');

        foreach ($torrents as $nb => $torrent) {
            $progressBar->advance(1);

            $torrentInfo[] = ['name' => $torrent[1], 'hash' => $torrent[0]];
            $f_param = [$torrent[0], '', 'f.path=', 'f.size_bytes='];
            $files = $this->rtorrent->call('f.multicall', $f_param);

            foreach ($files as $file) {
                $fullPath = "{$torrent[2]}/{$file[0]}";
                $torrentInfo[$nb]['files'][] = [
                    'name' => $fullPath,
                    'size' => Str::convertFileSize($file[1], 2)
                ];
                $torrentFile[] = $fullPath;
            }
        }

        $progressBar->setMessage('<fg=green>completed successfully!</>', 'status');
        $progressBar->finish();

        return ['path' => $torrentFile, 'info' => $torrentInfo];
    }

    public function listingFromHome($exclude)
    {
        $finder = new Finder();
        $finder->in($this->home)->files();

        if (! empty($exclude)) {
            $finder->notName($exclude);
        }

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
