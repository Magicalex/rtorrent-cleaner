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
            ->addArgument(
                'scgi',
                InputArgument::REQUIRED,
                'Set the scgi hostname:port or socket file of rtorrent. hostname: 127.0.0.1:5000 or socket: /run/rtorrent/rpc.socket')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'Set a folder to move unnecessary files')
            ->addOption(
                'exclude-files',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes files with a pattern. ex: --exclude-files=*.sub exclude all subfiles')
            ->addOption(
                'exclude-dirs',
                'd',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Excludes directories (must be relative to the default rtorrent directory). ex: --exclude-dirs=doc exclude the doc/ directory')
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
                'Move all the files without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('mv');
        $console = new Output($output, $input->getOption('log'));
        Helpers::title('rtorrent-cleaner - move unnecessary files', $console);
        $scgi = Helpers::scgiArgument($input->getArgument('scgi'));

        if (!is_dir($input->getArgument('folder'))) {
            Helpers::errorMessage('Please, define a correct directory.', $console);
            exit(1);
        } else {
            $folder = realpath($input->getArgument('folder'));
        }

        $cleaner = new Cleaner(
            $scgi['hostname'],
            $scgi['port'],
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
            $console->writeln(['', '> '.$nbFileNotTracked.' unnecessary files to move.']);
            foreach ($filesNotTracked as $file) {
                $fileName = basename($file['absolute_path']);
                if ($input->getOption('assume-yes') === true) {
                    rename($file['absolute_path'], $folder.'/'.$fileName);
                    $viewFile = Helpers::truncate($file['absolute_path']);
                    $console->writeln('file: <fg=yellow>'.$viewFile.'</> has been moved.');
                } elseif (!$input->getOption('assume-yes')) {
                    $viewFile = Helpers::truncate($file['absolute_path'], 70);
                    $ask = PHP_EOL.'<options=bold>Do you want move: <fg=green;options=bold,underscore>'.$viewFile.'</> ? (defaults: no)</>';
                    $question = new ChoiceQuestion($ask, ['yes', 'no', 'quit'], 1);
                    $question->setErrorMessage('Option %s is invalid.');
                    $answer = $helper->ask($input, $output, $question);

                    if ($answer == 'yes') {
                        rename($file['absolute_path'], $folder.'/'.$fileName);
                        $viewFile = Helpers::truncate($file['absolute_path']);
                        $console->writeln('file: <fg=yellow>'.$viewFile.'</> has been moved.');
                    } elseif ($answer == 'no') {
                        $console->writeln('<fg=yellow>file not moved.</>');
                    } elseif ($answer == 'quit') {
                        break;
                    }
                }
            }
        }

        $date = date('D, j M Y H:i:s');
        $torrents = $cleaner->getnumTorrents();
        $space = Helpers::convertFileSize($cleaner->getFreeDiskSpace(), 2);
        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $console->writeln(['', '> time: '.$time.', torrents: '.$torrents.', free space: '.$space.', date: '.$date]);
    }
}
