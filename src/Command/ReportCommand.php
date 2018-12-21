<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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
                '/data/torrents')
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_REQUIRED,
                'Exclude files with a pattern ex: --exclude=*.sub exclude all subfiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('report');

        $output->writeln([
            '==========',
            '= <fg=yellow>REPORT</> =',
            '==========',
            '',
            ' -> Retrieving the list of torrents files from rtorrent',
            ''
        ]);

        $exclude = Str::getPattern($input->getOption('exclude'));
        $list = new ListingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);
        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);
        $unnecessaryFile = count($notTracked);
        $nbMissingFile = count($missingFile);
        $unnecessaryTotalSize = 0;
        $output->writeln([" -> <fg=red>{$unnecessaryFile} file(s) are not tracked by rtorrent.</> (Use the `rm` command for remove unnecessary file)", '']);

        // display files not tracked by rtorrent
        $i = 0;
        foreach ($notTracked as $file) {
            $i++;
            $size = filesize($file);
            $unnecessaryTotalSize = $unnecessaryTotalSize + $size;
            $size = Str::convertFileSize($size, 2);
            $file = Str::truncate($file);
            $dataTable1[] = ["$i", "<fg=yellow>{$file}</>", "<fg=yellow>{$size}</>"];
        }

        if ($unnecessaryFile == 0) {
            $output->writeln('<fg=yellow>no files not tracked by rtorrent</>');
        } else {
            $unnecessaryTotalSize = Str::convertFileSize($unnecessaryTotalSize, 2);
            array_push($dataTable1, new TableSeparator(), ['', "<fg=green>Total recoverable space</>", "<fg=yellow>{$unnecessaryTotalSize}</>"]);
            $table = new Table($output);
            $table->setHeaders(['', 'Unnecessary files', 'Size'])->setRows($dataTable1);
            $table->render();
        }

        $output->writeln(['', " -> <fg=red>{$nbMissingFile} files(s) are missing in the torrents.</> (Use the `torrents` command for manage torrents with missing files)", '']);

        // display files missing from a torrent
        $i = 0;
        foreach ($missingFile as $file) {
            $i++;
            $file = Str::truncate($file);
            $dataTable2[] = ["$i", "<fg=yellow>{$file}</>"];
        }

        if ($nbMissingFile == 0) {
            $output->writeln('<fg=yellow>no missing files</>');
        } else {
            $table = new Table($output);
            $table->setHeaders(['', 'Missing files'])->setRows($dataTable2);
            $table->render();
        }

        $event = $time->stop('report');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
