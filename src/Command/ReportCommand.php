<?php

namespace Rtcleaner\Command;

use Rtcleaner\Cleaner;
use Rtcleaner\Helpers;
use Rtcleaner\Log\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ReportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('report')
            ->setDescription('Create a report on unnecessary files and missing files')
            ->setHelp('Command report for create a report on unnecessary files and missing files')
            ->addArgument(
                'scgi',
                InputArgument::REQUIRED,
                'Set the scgi hostname:port or socket file of rtorrent')
            ->addOption(
                'exclude-files',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes files with a pattern')
            ->addOption(
                'exclude-dirs',
                'd',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes directories, must be relative to the default rtorrent directory')
            ->addOption(
                'log',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Log output console in a file',
                false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('report');
        $console = new Output($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner - report', $console);
        $scgi = Helpers::scgiArgument($input->getArgument('scgi'));

        $cleaner = new Cleaner(
            $scgi['hostname'],
            $scgi['port'],
            $input->getOption('exclude-files'),
            $input->getOption('exclude-dirs'),
            $output
        );

        $console->writeln('');
        $filesNotTracked = $cleaner->getFilesNotTracked();
        $nbFileNotTracked = count($filesNotTracked);

        if ($nbFileNotTracked > 0) {
            $rows = [];
            $totalSize = $i = 0;
            foreach ($filesNotTracked as $file) {
                $totalSize += $file['size'];
                $rows[] = [
                    ++$i,
                    Helpers::truncate($file['absolute_path']),
                    Helpers::convertFileSize($file['size'], 2)
                ];
            }

            $console->table(
                ['', '<fg=yellow>'.$nbFileNotTracked.' files are not tracked by rtorrent</>', '<fg=yellow>Size</>'],
                $rows,
                ['', '<fg=yellow>Total recoverable space</>', '<fg=yellow>'.Helpers::convertFileSize($totalSize, 2).'</>']
            );
        } else {
            $console->writeln('> <fg=green>There is no file that is not tracked by rtorrent.</>');
        }

        $console->writeln('');
        $missingFile = $cleaner->getMissingFiles();
        $nbmissingFile = count($missingFile);

        if ($nbmissingFile > 0) {
            $rows = [];
            $totalSize = $i = 0;

            foreach ($missingFile as $torrent) {
                foreach ($torrent['files'] as $file) {
                    $totalSize += $file['size'];
                    $rows[] = [
                        ++$i,
                        Helpers::truncate($file['name'], 50),
                        Helpers::truncate($torrent['torrent'], 50),
                        Helpers::convertFileSize($file['size'], 2)
                    ];
                }
            }

            $console->table(
                ['', '<fg=yellow>'.$i.' files are missing</>', '<fg=yellow>Torrent</>', '<fg=yellow>Size</>'],
                $rows,
                ['', new TableCell('<fg=yellow>Total space to download</>', ['colspan' => 2]), '<fg=yellow>'.Helpers::convertFileSize($totalSize, 2).'</>']
            );
        } else {
            $console->writeln('> <fg=green>No missing files.</>');
        }

        $date = (new \DateTime())->format('D, j M Y H:i:s');
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $console->writeln(['', '> time: '.$time.', torrents: '.$torrents.', free space: '.$space.', date: '.$date]);

        return 0;
    }
}
