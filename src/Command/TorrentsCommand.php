<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Rtorrent\MissingFile;
use Rtorrent\Cleaner\Utils\Str;
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
                'scgi',
                null,
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent. ex: 127.0.0.1',
                '127.0.0.1')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the scgi port of rtorrent',
                5000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('missingFile');

        $output->writeln([
            '╔═════════════════════════════════════════╗',
            '║ RTORRENT-CLEANER - <fg=cyan>MANAGE MISSING FILES</> ║',
            '╚═════════════════════════════════════════╝',
            ''
        ]);

        $list = new MissingFile($input->getOption('scgi'), $input->getOption('port'));
        $data = $list->listingFromRtorrent($output);
        $missingFile = $list->getFilesMissingFromTorrent($data['rtorrent'], $data['local']);

        $nbFile = count($missingFile);
        $helper = $this->getHelper('question');
        $output->writeln(['', "> {$nbFile} file(s) are missing in the torrents.", '']);

        if ($nbFile == 0) {
            $output->writeln('<fg=yellow>no missing files</>');
        } else {
            $torrentMissingFile = $list->listTorrentMissingFile($missingFile, $data);

            foreach ($torrentMissingFile as $torrent) {
                $ask = "<options=bold>What do you want to do for the torrent <fg=yellow>{$torrent['name']}</> ? (defaults: nothing)</>\n";
                foreach ($torrent['files'] as $file) {
                    $file = Str::truncate($file);
                    $ask .= "missing file: <fg=cyan>{$file}</>\n";
                }

                $question = new ChoiceQuestion($ask, ['delete', 'redownload', 'nothing'], 2);
                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'delete') {
                    $list->deleteTorrent($torrent['hash']);
                    $output->writeln("torrent: <fg=yellow>{$torrent['name']}</> was deleted without the data");
                } elseif ($answer == 'redownload') {
                    $list->redownload($torrent['hash']);
                    $output->writeln("torrent: <fg=yellow>{$torrent['name']}</> download has been launched");
                } elseif ($answer == 'nothing') {
                    $output->writeln('<fg=yellow>torrent ignored</>');
                }
            }
        }

        $event = $time->stop('missingFile');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($data['data-torrent']);
        $output->writeln(['', "> time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
