<?php

namespace Rtcleaner\Command;

use Rtcleaner\Cleaner;
use Rtcleaner\Helpers;
use Rtcleaner\Log\Output;
use Symfony\Component\Console\Command\Command;
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
            ->addOption(
                'scgi',
                'u',
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the scgi port of rtorrent',
                -1)
            ->addOption(
                'exclude-files',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes files with a pattern. ex: --exclude-files=*.sub exclude all subfiles')
            ->addOption(
                'exclude-dirs',
                'd',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes directories. ex: --exclude-dirs=doc exclude the doc/ directory')
            ->addOption(
                'log',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Log output console in a file. ex: --log=/var/log/rtorrent-cleaner.log',
                false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('report');
        $console = new Output($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner - report', $console);

        $cleaner = new Cleaner(
            $input->getOption('scgi'),
            $input->getOption('port'),
            $input->getOption('exclude-files'),
            $input->getOption('exclude-dirs'),
            $output
        );

        $console->writeln('');
        $filesNotTracked = $cleaner->getFilesNotTracked();
        $nbFileNotTracked = count($filesNotTracked);

        if ($nbFileNotTracked > 0) {
            $rows = [];
            $totalSize = 0;
            foreach ($filesNotTracked as $i => $file) {
                $totalSize += $file['size'];
                $rows[] = [
                    $i + 1,
                    Helpers::truncate($file['absolute_path']),
                    Helpers::convertFileSize($file['size'], 2)
                ];
            }

            $console->table(
                ['', "<fg=yellow>{$nbFileNotTracked} file(s) are not tracked by rtorrent</>", '<fg=yellow>size</>'],
                $rows,
                ['', '<fg=yellow>Total recoverable space</>', '<fg=yellow>'.Helpers::convertFileSize($totalSize).'</>']
            );
        } else {
            $console->writeln('> <fg=green>There is no file that is not tracked by rtorrent.</>');
        }

        $console->writeln('');
        $missingFile = $cleaner->getFilesMissingFromRtorrent();
        $nbmissingFile = count($missingFile);

        if ($nbmissingFile > 0) {
            $rows = [];
            $totalSize = 0;
            foreach ($missingFile as $i => $file) {
                $totalSize += $file['size'];
                $rows[] = [
                    $i + 1,
                    Helpers::truncate($file['absolute_path']),
                    Helpers::convertFileSize($file['size'], 2)
                ];
            }

            $console->table(
                ['', "<fg=yellow>{$nbmissingFile} files(s) are missing</>", '<fg=yellow>size</>'],
                $rows,
                ['', '<fg=yellow>Total space to download</>', '<fg=yellow>'.Helpers::convertFileSize($totalSize).'</>']
            );
        } else {
            $console->writeln('> <fg=green>No missing files.</>');
        }

        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
