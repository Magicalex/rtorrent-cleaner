<?php

namespace RtorrentCleaner\Command;

use ByteUnits\Binary;
use RtorrentCleaner\Utils\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RtorrentListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('report')
            ->setDescription('create a report on unnecessary files')
            ->setHelp('create a report on unnecessary files')
            ->addOption(
                'url-xmlrcp',
                null,
                InputOption::VALUE_REQUIRED,
                'set url to your scgi mount point like: http://user:pass@localhost:80/RCP',
                'http://localhost:80/RCP')
            ->addOption(
                'home',
                null,
                InputOption::VALUE_REQUIRED,
                'set folder of your home like: /home/user/torrents',
                '/data'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '========================',
            '= <fg=cyan>LIST OF ALL TORRENTS</> =',
            '========================',
            '' // empty line
        ]);

        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrcp'));
        $data_rtorrent = $list->listing_from_rtorrent($output);
        $data_home = $list->listing_from_home();

        // display torrents infos
        foreach ($data_rtorrent['info'] as $key => $value) {
            $nb = $key;
            $name = $value['name'];
            $nb_files = $value['nb_files'];
            $output->writeln("[{$nb}] <fg=green>Torrent:</> <fg=yellow>{$name}</> (files: <fg=yellow>{$nb_files}</>)");

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

        $notTracked = $list->getFilesNotTracked($data_home, $data_rtorrent['path']);
        $missingFile = $list->getFilesMissingFromTorrent($data_rtorrent['path'], $data_home);

        $unnecessary_file = count($notTracked);
        $unnecessary_total_size = 0;

        $output->writeln([
            " -> <fg=red>There are {$unnecessary_file} file(s) not tracked by rtorrent</>",
            '' // empty line
        ]);

        // display files not tracked by rtorrent
        foreach ($notTracked as $file) {
            $size = filesize($file);
            $unnecessary_total_size = $unnecessary_total_size + $size;
            $size = Binary::bytes($size)->format(2);
            $trunc = Str::truncate($file);
            $output->writeln("file: <fg=red>{$trunc}</> size: <fg=yellow>{$size}</>");
        }

        $unnecessary_total_size = Binary::bytes($unnecessary_total_size)->format(2);

        $output->writeln([
            '', // empty line
            "<fg=green>Total recoverable space:</> <fg=yellow>{$unnecessary_total_size}</>",
            '' // empty line
        ]);

        if (count($missingFile) > 0) {
            $output->writeln([
                ' -> <fg=red>Files missing from a torrent</>',
                '' // empty line
            ]);

            // display files missing from a torrent
            foreach ($missingFile as $file) {
                $trunc = Str::truncate($file);
                $output->writeln("missing file: <fg=red>{$trunc}</>");
            }
        }
    }
}
