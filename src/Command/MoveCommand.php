<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Stopwatch\Stopwatch;

class MoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('mv')
            ->setDescription('Move your unnecessary files in a specified folder')
            ->setHelp('Command mv for move your unnecessary files in a specified folder')
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
                'Exclude files with a pattern ex: --exclude=*.sub exclude all subfiles')
            ->addOption(
                'assume-yes',
                null,
                InputOption::VALUE_NONE,
                'Move all the files without confirmation')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'Set a folder to move unnecessary files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('move');

        $output->writeln([
            '==========================',
            '= <fg=cyan>MOVE UNNECESSARY FILES</> =',
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
        $list = new ListingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);
        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);
        $helper = $this->getHelper('question');

        // move files not tracked
        foreach ($notTracked as $file) {
            $viewFile = Str::truncate($file, 70);
            $fileName = basename($file);

            if ($input->getOption('assume-yes') === true) {
                rename($file, $folder.'/'.$fileName);
                $output->writeln(" -> file: <fg=red>{$viewFile}</> has been moved");
            } elseif ($input->getOption('assume-yes') === false) {
                $question = new ChoiceQuestion(
                    "Do you want move <fg=yellow>{$viewFile}</> ? (defaults: no)",
                    ['yes', 'no'], 1
                );

                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'yes') {
                    rename($file, $folder.'/'.$fileName);
                    $output->writeln("file: <fg=green>{$viewFile}</> has been moved");
                } elseif ($answer == 'no') {
                    $output->writeln('<fg=yellow>file not moved</>');
                }
            }
        }

        if (count($notTracked) == 0) {
            $output->writeln('<fg=yellow>no files to move</>');
        }

        $event = $time->stop('move');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
