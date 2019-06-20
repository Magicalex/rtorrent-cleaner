<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Cleaner;
use Rtorrent\Cleaner\Helpers;
use Rtorrent\Cleaner\Log\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Stopwatch\Stopwatch;

class RemoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rm')
            ->setDescription('Delete your unnecessary files in your download folder')
            ->setHelp('Command rm for delete your unnecessary files in your download folder')
            ->addOption(
                'scgi',
                'u',
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent',
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
                'l',
                InputOption::VALUE_OPTIONAL,
                'Log output console in a file. ex: --log=/var/log/rtorrent-cleaner.log',
                false)
            ->addOption(
                'assume-yes',
                'y',
                InputOption::VALUE_NONE,
                'Delete all the files without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('rm');
        $console = new Log($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner • <fg=cyan>remove unnecessary files</>', $console);

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
            $console->writeln(['', '> <fg=green>No files to remove.</>']);
        } else {
            $console->writeln(['', "> {$nbFileNotTracked} unnecessary file(s) to delete.", '']);
            foreach ($filesNotTracked as $file) {
                if ($input->getOption('assume-yes') === true) {
                    unlink($file['full_path']);
                    $viewFile = Helpers::truncate($file['full_path']);
                    $console->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
                } else {
                    $viewFile = Helpers::truncate($file['full_path'], 70);
                    $question = new ChoiceQuestion(
                        "Do you want delete <fg=yellow>{$viewFile}</> ? (defaults: no)",
                        ['yes', 'no'], 1
                    );

                    $question->setErrorMessage('Option %s is invalid.');
                    $answer = $helper->ask($input, $output, $question);

                    if ($answer == 'yes') {
                        unlink($file['full_path']);
                        $viewFile = Helpers::truncate($file['full_path']);
                        $console->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
                    } elseif ($answer == 'no') {
                        $console->writeln('<fg=yellow>file not deleted</>');
                    }
                }
            }
        }

        $emptyDirectory = $cleaner->getEmptyDirectory();

        if (count($emptyDirectory) === 0) {
            $console->writeln('> <fg=green>No empty directory.</>');
        } else {
            while (count($emptyDirectory) > 0) {
                $removedDirectory = $cleaner->removeDirectory($emptyDirectory);

                foreach ($removedDirectory as $folder) {
                    $console->writeln("directory: <fg=yellow>{$folder}</> has been removed");
                }

                $emptyDirectory = $cleaner->getEmptyDirectory();
            }
        }

        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
