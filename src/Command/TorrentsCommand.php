<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\MissingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Stopwatch\Stopwatch;

class TorrentsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('torrents')
            ->setDescription('Delete torrents or redownload the missing files')
            ->setHelp('Command torrents for delete torrents or redownload the missing files')
            ->addOption(
                'url-xmlrpc',
                null,
                InputOption::VALUE_REQUIRED,
                'Set url to your scgi mount point like: http(s)://username:password@localhost:80/RPC',
                'http://rtorrent:8080/RPC');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('missingFile');

        $output->writeln([
            '========================',
            '= <fg=yellow>MANAGE MISSING FILES</> =',
            '========================',
            '',
            ' > Retrieving the list of torrents files from rtorrent',
            ''
        ]);

        $list = new MissingFile($input->getOption('url-xmlrpc'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome();
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);
        $nbFile = count($missingFile);
        $helper = $this->getHelper('question');

        $output->writeln([" > {$nbFile} file(s) are missing in the torrents.", '']);

        if ($nbFile == 0) {
            $output->writeln('<fg=yellow>no missing files</>');
        } else {
            $torrentMissingFile = $list->listTorrentMissingFile($missingFile, $dataRtorrent);

            foreach ($torrentMissingFile as $torrent) {
                $output->writeln("torrent: <fg=yellow>{$torrent['name']}</>");

                foreach ($torrent['files'] as $file) {
                    $file = Str::truncate($file);
                    $output->writeln(" -> missing file: <fg=cyan>{$file}</>");
                }
            }

            echo PHP_EOL;

            foreach ($torrentMissingFile as $torrent) {
                $question = new ChoiceQuestion(
                    "What do you want to do for the torrent <fg=yellow>{$torrent['name']}</> ? (defaults: nothing)",
                    ['delete', 'redownload', 'nothing'], 2
                );

                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'delete') {
                    $list->deleteTorrent($torrent['hash']);
                    $output->writeln("torrent: <fg=red>{$torrent['name']}</> has been removed");
                } elseif ($answer == 'redownload') {
                    $list->redownload($torrent['hash']);
                    $output->writeln("torrent: <fg=red>{$torrent['name']}</> has been redownloaded");
                } elseif ($answer == 'nothing') {
                    $output->writeln('<fg=yellow>torrent ignored</>');
                }
            }
        }

        $event = $time->stop('missingFile');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', " > time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
