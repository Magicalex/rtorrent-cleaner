<?php

namespace Rtcleaner\Command;

use Rtcleaner\Cleaner;
use Rtcleaner\Helpers;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument(
                'scgi',
                InputArgument::REQUIRED,
                'Set the scgi hostname:port or socket file of rtorrent');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('torrent');
        Helpers::title('rtorrent-cleaner - manage missing files', $output);
        $scgi = Helpers::scgiArgument($input->getArgument('scgi'));

        $cleaner = new Cleaner(
            $scgi['hostname'],
            $scgi['port'],
            null,
            null,
            $output
        );

        $missingFile = $cleaner->getMissingFiles();
        $nbTorrent = count($missingFile);
        $helper = $this->getHelper('question');

        if ($nbTorrent == 0) {
            $output->writeln(['', '> <fg=green>No missing files</>']);
        } else {
            $output->writeln(['', '> Files are missing for '.$nbTorrent.' torrents.']);
            foreach ($missingFile as $torrent) {
                $ask = PHP_EOL.'<options=bold>What do you want to do for the torrent: <options=bold,underscore>'.$torrent['torrent'].'</> ? (defaults: nothing)</>'.PHP_EOL;

                foreach ($torrent['files'] as $file) {
                    $filename = Helpers::truncate($file['name']);
                    $size = Helpers::convertFileSize($file['size'], 2);
                    $ask .= '> file: <fg=cyan>'.$filename.'</> size: <fg=cyan>'.$size.'</>'.PHP_EOL;
                }

                $question = new ChoiceQuestion($ask, ['nothing', 'delete', 'redownload', 'quit'], 0);
                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'delete') {
                    $cleaner->deleteTorrent($torrent['hash']);
                    $output->writeln('torrent: <fg=yellow>'.$torrent['torrent'].'</> was deleted without the data');
                } elseif ($answer == 'redownload') {
                    $cleaner->redownload($torrent['hash']);
                    $output->writeln('torrent: <fg=yellow>'.$torrent['torrent'].'</> download has been launched');
                } elseif ($answer == 'nothing') {
                    $output->writeln('<fg=yellow>torrent ignored</>');
                } elseif ($answer == 'quit') {
                    break;
                }
            }
        }

        $date = (new \DateTime())->format('D, j M Y H:i:s');
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $output->writeln(['', '> time: '.$time.', torrents: '.$torrents.', free space: '.$space.', date: '.$date]);
    }
}
