<?php

namespace Rtcleaner\Command;

use Rtcleaner\Cleaner;
use Rtcleaner\Helpers;
use Rtcleaner\Log\Output;
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
                'u',
                InputOption::VALUE_REQUIRED,
                'Set the scgi url of rtorrent')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the scgi port of rtorrent',
                -1)
            ->addOption(
                'exclude-files',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes files with a pattern. ex: --exclude-files=*.sub exclude all subfiles')
            ->addOption(
                'exclude-dirs',
                'd',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes directories (must be relative to directory default of rtorrent). ex: --exclude-dirs=doc exclude the doc/ directory')
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
                'Move all the files without confirmation')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'Set a folder to move unnecessary files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('mv');
        $console = new Output($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner - move unnecessary files', $console);

        if (!is_dir($input->getArgument('folder'))) {
            Helpers::errorMessage('Please, define a correct directory.', $console);
            exit(1);
        } else {
            $folder = realpath($input->getArgument('folder'));
        }

        $cleaner = new Cleaner(
            $input->getOption('scgi'),
            $input->getOption('port'),
            $input->getOption('exclude-files'),
            $input->getOption('exclude-dirs'),
            $output
        );

        $filesNotTracked = $cleaner->getFilesNotTracked();
        $nbFileNotTracked = count($filesNotTracked);
        $helper = $this->getHelper('question');

        if ($nbFileNotTracked === 0) {
            $console->writeln(['', '> <fg=green>No files to move.</>']);
        } else {
            $console->writeln(['', "> {$nbFileNotTracked} unnecessary files to move."]);
            foreach ($filesNotTracked as $file) {
                $fileName = basename($file['absolute_path']);
                if ($input->getOption('assume-yes') === true) {
                    rename($file['absolute_path'], $folder.'/'.$fileName);
                    $viewFile = Helpers::truncate($file['absolute_path']);
                    $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved.");
                } elseif (!$input->getOption('assume-yes')) {
                    $viewFile = Helpers::truncate($file['absolute_path'], 70);
                    $ask = PHP_EOL.'<options=bold>Do you want move: <fg=green;options=bold,underscore>'.$viewFile.'</> ? (defaults: no)</>';
                    $question = new ChoiceQuestion($ask, ['yes', 'no'], 1);
                    $question->setErrorMessage('Option %s is invalid.');
                    $answer = $helper->ask($input, $output, $question);

                    if ($answer == 'yes') {
                        rename($file['absolute_path'], $folder.'/'.$fileName);
                        $viewFile = Helpers::truncate($file['absolute_path']);
                        $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved.");
                    } elseif ($answer == 'no') {
                        $console->writeln('<fg=yellow>file not moved.</>');
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
