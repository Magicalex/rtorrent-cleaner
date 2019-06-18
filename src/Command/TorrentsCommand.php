<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Cleaner;
use Rtorrent\Cleaner\Helpers;
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
                'u',
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent',
                '127.0.0.1')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the scgi port of rtorrent',
                -1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('torrent');
        Helpers::title('rtorrent-cleaner • <fg=cyan>manage missing files</>', $output);

        $cleaner = new Cleaner(
            $input->getOption('scgi'),
            $input->getOption('port'),
            null,
            $output
        );

        $missingFile = $cleaner->getTorrentsMissingFile();
        $helper = $this->getHelper('question');
        $output->writeln(['', '> '.$missingFile['nb'].' file(s) are missing.', '']);

        if ($missingFile['nb'] == 0) {
            $output->writeln('<fg=yellow>no missing files</>');
        } else {
            foreach ($missingFile['data'] as $torrent) {
                $ask = '<options=bold>What do you want to do for the torrent <fg=yellow>'.$torrent['name'].'</> ? (defaults: nothing)</>'.PHP_EOL;

                foreach ($torrent['file'] as $data) {
                    $file = Helpers::truncate($data['full_path']);
                    $size = Helpers::convertFileSize($data['size'], 2);
                    $ask .= '> file: <fg=green>'.$file.'</> size: <fg=green>'.$size.'</>'.PHP_EOL;
                }

                $question = new ChoiceQuestion($ask, ['nothing', 'delete', 'redownload'], 0);
                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'delete') {
                    $cleaner->deleteTorrent($torrent['hash']);
                    $output->writeln("torrent: <fg=yellow>{$torrent['name']}</> was deleted without the data");
                } elseif ($answer == 'redownload') {
                    $cleaner->redownload($torrent['hash']);
                    $output->writeln("torrent: <fg=yellow>{$torrent['name']}</> download has been launched");
                } elseif ($answer == 'nothing') {
                    $output->writeln('<fg=yellow>torrent ignored</>');
                }
            }
        }

        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $output->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
