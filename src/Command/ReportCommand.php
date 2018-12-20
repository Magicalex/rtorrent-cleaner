<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
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
            ->setDescription('Create a report on unnecessary files')
            ->setHelp('Create a report on unnecessary files')
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
                InputArgument::OPTIONAL,
                'Exclude files with a pattern ex: --exclude=*.sub,*.str exclude all subfiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('report');

        $output->writeln([
            '==========',
            '= <fg=cyan>REPORT</> =',
            '==========',
            '',
            ' -> <fg=green>Retrieving the list of files.</>',
            ''
        ]);

        $exclude = Str::getPattern($input->getOption('exclude'));
        $list = new ListingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);

        if ($output->isVerbose()) {
            $output->writeln([
                '--------------------',
                '<fg=cyan>LIST OF ALL TORRENTS</>',
                '--------------------',
                ''
            ]);

            // display torrents infos
            foreach ($dataRtorrent['info'] as $key => $value) {
                $nb = $key;
                $name = $value['name'];
                $output->writeln("[{$nb}] <fg=green>Torrent:</> <fg=yellow>{$name}</>");

                foreach ($value['files'] as $key => $value) {
                    $f_id = $key;
                    $file = $value['name'];
                    $size = $value['size'];
                    $output->writeln("{$f_id}: <fg=cyan>{$file}</> (size: <fg=yellow>{$size}</>)");
                }
            }
        }

        $output->writeln([
            '-------------------------------------------',
            '<fg=cyan>LIST OF GAPS BETWEEN RTORRENT AND YOUR HOME</>',
            '-------------------------------------------',
            ''
        ]);

        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);
        $unnecessaryFile = count($notTracked);
        $nbMissingFile = count($missingFile);
        $unnecessaryTotalSize = 0;
        $output->writeln([" -> <fg=red>There are {$unnecessaryFile} file(s) not tracked by rtorrent.</> (Use rm command for remove unnecessary file)", '']);

        // display files not tracked by rtorrent
        foreach ($notTracked as $file) {
            $size = filesize($file);
            $unnecessaryTotalSize = $unnecessaryTotalSize + $size;
            $size = Str::convertFileSize($size, 2);
            $file = Str::truncate($file);
            $output->writeln("unnecessary file: <fg=red>{$file}</> size: <fg=yellow>{$size}</>");
        }

        if ($unnecessaryFile == 0) {
            $output->writeln('<fg=yellow>no files not tracked by rtorrent</>');
        }

        $unnecessaryTotalSize = Str::convertFileSize($unnecessaryTotalSize, 2);
        $output->writeln(['', "<fg=green>Total recoverable space:</> <fg=yellow>{$unnecessaryTotalSize}</>"]);

        $output->writeln(['', " -> <fg=red>There are {$nbMissingFile} file(s) missing from a torrent.</> (Use torrents command for manage torrent with missing file)", '']);

        // display files missing from a torrent
        foreach ($missingFile as $file) {
            $file = Str::truncate($file);
            $output->writeln("missing file: <fg=yellow>{$file}</>");
        }

        if ($nbMissingFile == 0) {
            $output->writeln('<fg=yellow>no missing files</>');
        }

        $event = $time->stop('report');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
