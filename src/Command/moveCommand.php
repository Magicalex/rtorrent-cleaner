<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Utils\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class moveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('mv')
            ->setDescription('delete unnecessary files')
            ->setHelp('Command mv for move unnecessary files in a specified folder')
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
                'Exclude files with a pattern ex: --exclude=*.sub,*.str exclude all subfiles')
            ->addArgument(
                'folder',
                null,
                InputArgument::REQUIRED,
                'Set folder where to move unnecessary files')
            ->addOption(
                'assume-yes',
                null,
                InputOption::VALUE_NONE,
                'move all the files without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '==========================',
            '= <fg=green>MOVE UNNECESSARY FILES</> =',
            '==========================',
            '',
            ' -> <fg=green>Retrieving the list of concerned files.</>',
            ''
        ]);

        // check directory
        if (is_dir($input->getArgument('folder')) === false) {
            $output->writeln('<fg=red>Please, define a correct directory.</>');
            exit(1);
        } else {
            $folder = realpath($input->getArgument('folder'));
        }

        // exclude file with pattern
        $exclude = Str::getPattern($input->getOption('exclude'));

        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrpc'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);
        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);

        // move files not tracked
        foreach ($notTracked as $file) {
            $trunc = Str::truncate($file, 70);
            $fileName = basename($file);

            if ($input->getOption('assume-yes') === true) {
                rename($file, $folder.'/'.$fileName);
                $output->writeln(" -> file: <fg=red>{$trunc}</> has been moved");
            } elseif ($input->getOption('assume-yes') === false) {
                $helper = $this->getHelper('question');
                $question = new Question("Do you want move <fg=red>{$trunc}</> ? [y|n] ", 'n');

                if ($helper->ask($input, $output, $question) === 'y') {
                    rename($file, $folder.'/'.$fileName);
                    $output->writeln(" -> file: <fg=red>{$trunc}</> has been moved");
                } else {
                    $output->writeln(' -> file not moved');
                }
            }
        }
    }
}
