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

        $output->writeln([
            '========================',
            '= <fg=blue>MANAGE MISSING FILES</> =',
            '========================',
            '',
            ' -> <fg=green>Retrieving the list of concerned files.</>',
            ''
        ]);

        $list = new MissingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome();
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);
        $helper = $this->getHelper('question');

        if (count($missingFile) == 0) {
            $output->writeln(' -> <fg=yellow>no missing files</>');
        } else {
            $torrentMissingFile = [];
            $findHash = false;

            foreach ($missingFile as $file) {
                $torrent = $list->findTorrentHash($dataRtorrent['info'], $file);

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
                        'hash' => $torrent['hash'],
                        'name' => $torrent['name'],
                        'files' => [$file]
                    ];
                }

                $findHash = false;
            }

            foreach ($torrentMissingFile as $info) {
                $question = new Question("What do you want to do for the torrent <fg=yellow>{$info['name']}</> ? [delete|redownload|nothing] ", 'nothing');

                foreach ($info['files'] as $file) {
                    $file = Str::truncate($file);
                    $output->writeln(" -> file: <fg=blue>{$file}</> ");
                }

                if ($helper->ask($input, $output, $question) === 'delete') {
                    $output->writeln(" -> torrent: <fg=red>{$info['name']}</> has been removed");
                } elseif ($helper->ask($input, $output, $question) === 'redownload') {
                    $output->writeln(" -> torrent: <fg=red>{$info['name']}</> redownload");
                }
            }
        }

        $event = $time->stop('missingFile');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
