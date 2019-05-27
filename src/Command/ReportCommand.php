<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Log\Log;
use Rtorrent\Cleaner\Rtorrent\ListingFile;
use Rtorrent\Cleaner\Utils\Str;
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
        $time = new Stopwatch();
        $time->start('report');

        $console = new Log($output, $input->getOption('log'));

        $console->writeln([
            '╔═══════════════════════════╗',
            '║ RTORRENT-CLEANER - <fg=cyan>REPORT</> ║',
            '╚═══════════════════════════╝',
            ''
        ]);

        $list = new ListingFile($input->getOption('scgi'), $input->getOption('port'));
        $data = $list->listingFromRtorrent($output, $input->getOption('exclude'));

        $notTracked = $list->getFilesNotTracked($data['rtorrent'], $data['local']);
        $missingFile = $list->getFilesMissingFromTorrent($data['rtorrent'], $data['local']);

        $unnecessaryFile = count($notTracked);
        $nbMissingFile = count($missingFile);

        $unnecessaryTotalSize = 0;
        $console->writeln(['', "> {$unnecessaryFile} file(s) are not tracked by rtorrent. (use the `rm` or `mv` command)", '']);

        $i = 0;
        foreach ($notTracked as $file) {
            $i++;
            $size = filesize($file);
            $unnecessaryTotalSize = $unnecessaryTotalSize + $size;
            $size = Str::convertFileSize($size, 2);
            $file = Str::truncate($file);
            $dataTable1[] = [$i, $file, "<fg=yellow>{$size}</>"];
        }

        if ($unnecessaryFile == 0) {
            $console->writeln('<fg=yellow>no files not tracked by rtorrent</>');
        } else {
            $unnecessaryTotalSize = Str::convertFileSize($unnecessaryTotalSize, 2);
            array_push($dataTable1, new TableSeparator(), ['', '<fg=green>Total recoverable space</>', "<fg=yellow>{$unnecessaryTotalSize}</>"]);
            $console->table(['', 'Unnecessary files', 'Size'], $dataTable1);
        }

        $console->writeln(['', "> {$nbMissingFile} files(s) are missing in the torrents. (use the `torrents` command)", '']);

        $i = 0;
        foreach ($missingFile as $file) {
            $i++;
            $file = Str::truncate($file);
            $dataTable2[] = [$i, $file];
        }

        if ($nbMissingFile == 0) {
            $console->writeln('<fg=yellow>no missing files</>');
        } else {
            $console->table(['', 'Missing files'], $dataTable2);
        }

        $event = $time->stop('report');
        $time = Str::humanTime($event->getDuration());
        $torrents = count($data['data-torrent']);
        $space = Str::convertFileSize($data['free_space'], 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
