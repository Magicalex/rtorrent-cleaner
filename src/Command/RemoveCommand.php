<?php

namespace Rtorrent\Cleaner\Command;

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
                'url-xmlrpc',
                null,
                InputOption::VALUE_REQUIRED,
                'Set url to your scgi mount point like: http(s)://localhost:80/RPC',
                'http://rtorrent:8080/RPC')
            ->addOption(
                'exclude',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude files with a pattern. ex: --exclude=*.sub exclude all subfiles')
            ->addOption(
                'assume-yes',
                'y',
                InputOption::VALUE_NONE,
                'Delete all the files without confirmation')
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_REQUIRED,
                'Set username for a Basic HTTP authentication')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set password for a Basic HTTP authentication');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = new Stopwatch();
        $time->start('remove');

        $output->writeln([
            '╔═════════════════════════════════════════════╗',
            '║ RTORRENT-CLEANER - <fg=cyan>REMOVE UNNECESSARY FILES</> ║',
            '╚═════════════════════════════════════════════╝',
            ''
        ]);

        $list = new RemoveFile($input->getOption('url-xmlrpc'), $input->getOption('username'), $input->getOption('password'));
        $data = $list->listingFromRtorrent($output, $input->getOption('exclude'));
        $notTracked = $list->getFilesNotTracked($data['rtorrent'], $data['local']);

        $nbFile = count($notTracked);
        $helper = $this->getHelper('question');
        $output->writeln(['', "> {$nbFile} unnecessary file(s) to delete.", '']);

        foreach ($notTracked as $file) {
            if ($input->getOption('assume-yes') === true) {
                unlink($file);
                $viewFile = Str::truncate($file);
                $output->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
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
                    $output->writeln("file: <fg=yellow>{$viewFile}</> has been removed");
                } elseif ($answer == 'no') {
                    $output->writeln('<fg=yellow>file not deleted</>');
                }
            }
        }

        if (count($notTracked) == 0) {
            $output->writeln('<fg=yellow>no files to remove</>');
        }

        $emptyDirectory = $list->getEmptyDirectory();

        if (count($emptyDirectory) == 0) {
            $output->writeln('<fg=yellow>no empty directory</>');
        } else {
            while (count($emptyDirectory) > 0) {
                $removedDirectory = $list->removeDirectory($emptyDirectory);

                foreach ($removedDirectory as $folder) {
                    $output->writeln("directory: <fg=yellow>{$folder}</> has been removed");
                }

                $emptyDirectory = $list->getEmptyDirectory();
            }
        }

        $event = $time->stop('remove');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($data['data-torrent']);
        $output->writeln(['', "> time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
