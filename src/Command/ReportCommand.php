<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Helpers;
use Rtorrent\Cleaner\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
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
                null,
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent. ex: 127.0.0.1',
                '127.0.0.1')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the scgi port of rtorrent',
                -1)
            ->addOption(
                'exclude',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude files with a pattern. ex: --exclude=*.sub exclude all subfiles')
            ->addOption(
                'log',
                null,
                InputOption::VALUE_OPTIONAL,
                'Log output console in a file. ex: --log=/var/log/rtorrent-cleaner.log',
                false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('report');
        $console = new Log($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner • <fg=cyan>report</>', $output);

        $cleaner = new \Rtorrent\Cleaner\Cleaner(
            $input->getOption('scgi'),
            $input->getOption('port'),
            $input->getOption('exclude'),
            $output
        );

        $filesNotTracked = $cleaner->getFilesNotTracked();
        $missingFile = $cleaner->getFilesMissingFromRtorrent();
        $nbFileNotTracked = count($filesNotTracked);
        $nbmissingFile = count($missingFile);
        $totalSize = 0;
        $dataTable = [];
        $i = 0;

        foreach ($filesNotTracked as $file) {
            $i++;
            $totalSize = $totalSize + $file['size'];
            $size = Helpers::convertFileSize($file['size'], 2);
            $file = Helpers::truncate($file['full_path']);
            $dataTable[] = [$i, $file, $size];
        }

        if ($nbFileNotTracked == 0) {
            $console->writeln(['', '<fg=yellow>no files not tracked by rtorrent</>']);
        } else {
            $totalSize = Helpers::convertFileSize($totalSize, 2);
            array_push($dataTable, new TableSeparator(), ['', '<fg=yellow>Total recoverable space</>', "<fg=yellow>{$totalSize}</>"]);
            $console->writeln('');
            $console->table(['', "<fg=yellow>{$nbFileNotTracked} file(s) are not necessary for rtorrent</>", '<fg=yellow>Size</>'], $dataTable);
        }

        $totalSize = 0;
        $dataTable = [];
        $i = 0;

        foreach ($missingFile as $file) {
            $i++;
            $totalSize = $totalSize + $file['size'];
            $size = Helpers::convertFileSize($file['size'], 2);
            $file = Helpers::truncate($file['full_path']);
            $dataTable[] = [$i, $file, $size];
        }

        if ($nbmissingFile == 0) {
            $console->writeln('<fg=yellow>no missing files</>');
        } else {
            $totalSize = Helpers::convertFileSize($totalSize, 2);
            array_push($dataTable, new TableSeparator(), ['', '<fg=yellow>Total space to download</>', "<fg=yellow>{$totalSize}</>"]);
            $console->writeln('');
            $console->table(['', "<fg=yellow>{$nbmissingFile} files(s) are missing</>", '<fg=yellow>Size</>'], $dataTable);
        }

        $event = $time->stop('report');
        $time = Helpers::humanTime($event->getDuration());
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
