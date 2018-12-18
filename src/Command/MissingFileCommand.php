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

        // do something

        $event = $time->stop('missingFile');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
