<?php

namespace Rtorrent\Cleaner\Rtorrent;

use Rtorrent\Cleaner\Utils\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ListingFile extends Connect
{
    protected $directories = [];

    public function listingFromRtorrent(OutputInterface $output, $exclude = null)
    {
        $localFile = [];
        $d_param = ['', 'default', 'd.hash=', 'd.name=', 'd.directory='];
        $torrents = $this->rtorrent->call('d.multicall2', $d_param);

        $progressBar = new ProgressBar($output, count($torrents));
        $progressBar->setFormat(" %bar% %percent%%\n remaining time: <fg=yellow>%remaining%</>\n status: %status%\n");
        $progressBar->setMessage('<fg=yellow>getting the list of all torrents from rtorrent...</>', 'status');
        $progressBar->setBarCharacter('<fg=green>█</>');
        $progressBar->setEmptyBarCharacter('█');
        $progressBar->setProgressCharacter('<fg=green>█</>');
        $progressBar->start();
        $progressBar->setMessage('<fg=yellow>recovering the files list from rtorrent...</>', 'status');

        foreach ($torrents as $nb => $torrent) {
            if (is_dir($torrent[2]) === true) {
                $this->directories[] = $torrent[2];
            }

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

            $progressBar->advance(1);
        }

        $this->directories = array_unique($this->directories);

        if (count($this->directories) == 0) {
            $output->writeln([
                '',
                '<error>                                            </>',
                '<error>  Torrent files are not available locally.  </>',
                '<error>                                            </>'
            ]);

            exit(1);
        }

        $finder = new Finder();
        $finder->in($this->directories)->files();

        if ($exclude !== null) {
            $finder->notName($exclude);
        }

        foreach ($finder as $file) {
            $localFile[] = $file->getPathname();
        }

        $progressBar->setMessage('<fg=green>completed successfully!</>', 'status');
        $progressBar->finish();

        return [
            'rtorrent'     => $torrentFile,
            'data-torrent' => $torrentInfo,
            'local'        => array_unique($localFile)
        ];
    }

    public function getFilesNotTracked($rtorrent, $local)
    {
        return array_diff($local, $rtorrent);
    }

    public function getFilesMissingFromTorrent($rtorrent, $local)
    {
        return array_diff($rtorrent, $local);
    }
}
