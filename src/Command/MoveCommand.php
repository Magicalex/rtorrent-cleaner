<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Cleaner;
use Rtorrent\Cleaner\Helpers;
use Rtorrent\Cleaner\Log\Log;
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
                false)
            ->addOption(
                'assume-yes',
                'y',
                InputOption::VALUE_NONE,
                'Move all the files without confirmation')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'Set a folder to move unnecessary files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('mv');
        $console = new Log($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner • <fg=cyan>move unnecessary files</>', $console);

        if (!is_dir($input->getArgument('folder'))) {
            Helpers::errorMessage('Please, define a correct directory.', $console);
            exit(1);
        } else {
            $folder = realpath($input->getArgument('folder'));
        }

        $cleaner = new Cleaner(
            $input->getOption('scgi'),
            $input->getOption('port'),
            $input->getOption('exclude'),
            $output
        );

        $filesNotTracked = $cleaner->getFilesNotTracked();
        $nbFileNotTracked = count($filesNotTracked);
        $helper = $this->getHelper('question');

        if ($nbFileNotTracked === 0) {
            $console->writeln(['', '> <fg=yellow>No files to move.</>']);
        } else {
            $console->writeln(['', "> {$nbFileNotTracked} unnecessary file(s) to move.", '']);
            foreach ($filesNotTracked as $file) {
                $fileName = basename($file['full_path']);
                if ($input->getOption('assume-yes') === true) {
                    rename($file['full_path'], $folder.'/'.$fileName);
                    $viewFile = Helpers::truncate($file['full_path']);
                    $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved");
                } elseif (!$input->getOption('assume-yes')) {
                    $viewFile = Helpers::truncate($file['full_path'], 70);
                    $question = new ChoiceQuestion(
                        "Do you want move <fg=yellow>{$viewFile}</> ? (defaults: no)",
                        ['yes', 'no'], 1
                    );

                    $question->setErrorMessage('Option %s is invalid.');
                    $answer = $helper->ask($input, $output, $question);

                    if ($answer == 'yes') {
                        rename($file['full_path'], $folder.'/'.$fileName);
                        $viewFile = Helpers::truncate($file['full_path']);
                        $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved");
                    } elseif ($answer == 'no') {
                        $console->writeln('<fg=yellow>file not moved</>');
                    }
                }
            }
        }

        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
