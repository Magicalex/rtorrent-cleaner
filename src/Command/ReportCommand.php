<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Utils\ListingFile;
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
            '========================',
            '= <fg=cyan>LIST OF ALL TORRENTS</> =',
            '========================',
            '' // empty line
        ]);

        // exclude file with pattern
        $exclude = Str::getPattern($input->getOption('exclude'));
        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrpc'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);

        // display torrents infos
        foreach ($dataRtorrent['info'] as $key => $value) {
            $nb = $key;
            $name = $value['name'];
            $nbFiles = $value['nb_files'];
            $output->writeln("[{$nb}] <fg=green>Torrent:</> <fg=yellow>{$name}</> (files: <fg=yellow>{$nbFiles}</>)");

            if ($output->isVerbose()) {
                foreach ($value['files'] as $key => $value) {
                    $f_id = $key;
                    $file = $value['name'];
                    $size = $value['size'];
                    $output->writeln("{$f_id}: <fg=cyan>{$file}</> (size: <fg=yellow>{$size}</>)");
                }
            }
        }

        $output->writeln([
            '', // empty line
            '===============================================',
            '= <fg=cyan>LIST OF GAPS BETWEEN RTORRENT AND YOUR HOME</> =',
            '===============================================',
            '' // empty line
        ]);

        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);
        $missingFile = $list->getFilesMissingFromTorrent($dataRtorrent['path'], $dataHome);
        $unnecessaryFile = count($notTracked);
        $unnecessaryTotalSize = 0;

        $output->writeln([
            " -> <fg=red>There are {$unnecessaryFile} file(s) not tracked by rtorrent</>",
            '' // empty line
        ]);

        // display files not tracked by rtorrent
        foreach ($notTracked as $file) {
            $size = filesize($file);
            $unnecessaryTotalSize = $unnecessaryTotalSize + $size;
            $size = Str::convertFileSize($size, 2);
            $trunc = Str::truncate($file);
            $output->writeln("unnecessary file: <fg=red>{$trunc}</> size: <fg=yellow>{$size}</>");
        }

        $unnecessaryTotalSize = Str::convertFileSize($unnecessaryTotalSize, 2);

        $output->writeln([
            '', // empty line
            "<fg=green>Total recoverable space:</> <fg=yellow>{$unnecessaryTotalSize}</>",
            '' // empty line
        ]);

        if (($numberMissingFile = count($missingFile)) > 0) {
            $output->writeln([
                " -> <fg=red>There {$numberMissingFile} file(s) missing from a torrent</>",
                '' // empty line
            ]);

            // display files missing from a torrent
            foreach ($missingFile as $file) {
                $trunc = Str::truncate($file);
                $output->writeln("missing file: <fg=red>{$trunc}</>");
            }
        }

        $event = $time->stop('report');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $output->writeln(" -> time: {$time}, memory: {$mb}");
    }
}
