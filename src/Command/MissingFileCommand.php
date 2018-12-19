<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\MissingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Stopwatch\Stopwatch;

class MissingFileCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('torrent')
            ->setDescription('Management of missing files')
            ->setHelp('Command torrent for delete torrents or redownload the missing files')
            ->addOption(
                'url-xmlrpc',
                null,
                InputOption::VALUE_REQUIRED,
                'Set url to your scgi mount point like: http(s)://username:password@localhost:80/RPC',
                'http://rtorrent:8080/RPC')
            ->addOption(
                'home',
                null,
                InputOption::VALUE_REQUIRED,
                'Set folder of your home like: /home/user/torrents',
                '/data/torrents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('missingFile');

        $list = new MissingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome();
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);

        $torrentMissingFile = [];
        $findHash = false;

        foreach ($missingFile as $file) {
            $hash = $list->findTorrentHash($dataRtorrent['info'], $file);

            // check if $hash has been already add
            foreach ($torrentMissingFile as $id => $data) {
                if ($data['hash'] == $hash) {
                    $torrentMissingFile[$id]['files'][] = $file;
                    $findHash = true;
                    break;
                }
            }

            if ($findHash === false) {
                $torrentMissingFile[] = [
                    'hash' => $hash,
                    'files' => [$file]
                ];
            }

            $findHash = false;
        }
        var_dump($torrentMissingFile);

        // 3 - afficher que le nom du torrent
        //  -> Des fichiers manquent dans ce torrent (montrer la liste)
        // 4 - Proposer une suppression du torrent OU de redownload le torrent OU ne rien faire

        $event = $time->stop('missingFile');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
