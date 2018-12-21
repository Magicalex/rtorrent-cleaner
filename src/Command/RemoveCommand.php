<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Rtorrent\ListingFile;
use RtorrentCleaner\Utils\Directory;
use RtorrentCleaner\Utils\Str;
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
                'Delete all the files without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('remove');

        $output->writeln([
            '============================',
            '= <fg=yellow>REMOVE UNNECESSARY FILES</> =',
            '============================',
            '',
            ' > Retrieving the list of torrents files from rtorrent',
            ''
        ]);

        $exclude = Str::getPattern($input->getOption('exclude'));
        $list = new ListingFile($input->getOption('url-xmlrpc'), $input->getOption('home'));
        $dataRtorrent = $list->listingFromRtorrent($output);
        $dataHome = $list->listingFromHome($exclude);
        $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);
        $nbFile = count($notTracked);
        $helper = $this->getHelper('question');

        $output->writeln([" > {$nbyFile} unnecessary file(s) to delete.", '']);

        foreach ($notTracked as $file) {
            $viewFile = Str::truncate($file, 70);

            if ($input->getOption('assume-yes') === true) {
                unlink($file);
                $output->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
            } elseif ($input->getOption('assume-yes') === false) {
                $question = new ChoiceQuestion(
                    "Do you want delete <fg=yellow>{$viewFile}</> ? (defaults: no)",
                    ['yes', 'no'], 1
                );

                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'yes') {
                    unlink($file);
                    $output->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
                } elseif ($answer == 'no') {
                    $output->writeln('<fg=yellow>file not deleted</>');
                }
            }
        }

        if (count($notTracked) == 0) {
            $output->writeln('<fg=yellow>no files to remove</>');
        }

        // remove empty directory
        $directory = new Directory($input->getOption('home'));
        $emptyDirectory = $directory->getEmptyDirectory();

        if (count($emptyDirectory) == 0) {
            $output->writeln('<fg=yellow>no empty directory</>');
        } else {
            while (count($emptyDirectory) > 0) {
                $removedDirectory = $directory->removeDirectory($emptyDirectory);

                foreach ($removedDirectory as $folder) {
                    $output->writeln("empty directory: <fg=red>{$folder}</> has been removed");
                }

                $emptyDirectory = $directory->getEmptyDirectory();
            }
        }

        $event = $time->stop('remove');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($dataRtorrent['info']);
        $output->writeln(['', "time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
