<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Log\Log;
use Rtorrent\Cleaner\Rtorrent\RemoveFile;
use Rtorrent\Cleaner\Utils\Str;
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
                'Delete all the files without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('remove');

        $console = new Log($output, $input->getOption('log'));

        $console->writeln([
            '╔═════════════════════════════════════════════╗',
            '║ RTORRENT-CLEANER - <fg=cyan>REMOVE UNNECESSARY FILES</> ║',
            '╚═════════════════════════════════════════════╝',
            ''
        ]);

        $list = new RemoveFile($input->getOption('scgi'), $input->getOption('port'));
        $data = $list->listingFromRtorrent($output, $input->getOption('exclude'));
        $notTracked = $list->getFilesNotTracked($data['rtorrent'], $data['local']);

        $nbFile = count($notTracked);
        $helper = $this->getHelper('question');
        $console->writeln(['', "> {$nbFile} unnecessary file(s) to delete.", '']);

        foreach ($notTracked as $file) {
            if ($input->getOption('assume-yes') === true) {
                unlink($file);
                $viewFile = Str::truncate($file);
                $console->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
            } elseif ($input->getOption('assume-yes') === false) {
                $viewFile = Str::truncate($file, 70);
                $question = new ChoiceQuestion(
                    "Do you want delete <fg=yellow>{$viewFile}</> ? (defaults: no)",
                    ['yes', 'no'], 1
                );

                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'yes') {
                    unlink($file);
                    $viewFile = Str::truncate($file);
                    $console->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
                } elseif ($answer == 'no') {
                    $console->writeln('<fg=yellow>file not deleted</>');
                }
            }
        }

        if (count($notTracked) == 0) {
            $console->writeln('<fg=yellow>no files to remove</>');
        }

        $emptyDirectory = $list->getEmptyDirectory();

        if (count($emptyDirectory) == 0) {
            $console->writeln('<fg=yellow>no empty directory</>');
        } else {
            while (count($emptyDirectory) > 0) {
                $removedDirectory = $list->removeDirectory($emptyDirectory);

                foreach ($removedDirectory as $folder) {
                    $console->writeln("directory: <fg=yellow>{$folder}</> has been removed");
                }

                $emptyDirectory = $list->getEmptyDirectory();
            }
        }

        $event = $time->stop('remove');
        $time = Str::humanTime($event->getDuration());
        $torrents = count($data['data-torrent']);
        $space = Str::convertFileSize($data['free_space'], 2);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, free space: {$space}"]);
    }
}
