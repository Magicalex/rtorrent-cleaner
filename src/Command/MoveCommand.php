<?php

namespace Rtorrent\Cleaner\Command;

use Rtorrent\Cleaner\Log\Log;
use Rtorrent\Cleaner\Rtorrent\ListingFile;
use Rtorrent\Cleaner\Utils\Str;
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
        $time = new Stopwatch();
        $time->start('move');

        $console = new Log($output, $input->getOption('log'));

        $console->writeln([
            '╔═══════════════════════════════════════════╗',
            '║ RTORRENT-CLEANER - <fg=cyan>MOVE UNNECESSARY FILES</> ║',
            '╚═══════════════════════════════════════════╝',
            ''
        ]);

        if (is_dir($input->getArgument('folder')) === false) {
            $console->writeln([
                '<error>                                       </>',
                '<error>  Please, define a correct directory.  </>',
                '<error>                                       </>'
            ]);

            exit(1);
        } else {
            $folder = realpath($input->getArgument('folder'));
        }

        $list = new ListingFile($input->getOption('scgi'), $input->getOption('port'));
        $data = $list->listingFromRtorrent($output, $input->getOption('exclude'));
        $notTracked = $list->getFilesNotTracked($data['rtorrent'], $data['local']);

        $nbFile = count($notTracked);
        $helper = $this->getHelper('question');
        $console->writeln(['', "> {$nbFile} unnecessary file(s) to move.", '']);

        foreach ($notTracked as $file) {
            $fileName = basename($file);

            if ($input->getOption('assume-yes') === true) {
                rename($file, $folder.'/'.$fileName);
                $viewFile = Str::truncate($file);
                $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved");
            } elseif ($input->getOption('assume-yes') === false) {
                $viewFile = Str::truncate($file, 70);
                $question = new ChoiceQuestion(
                    "Do you want move <fg=yellow>{$viewFile}</> ? (defaults: no)",
                    ['yes', 'no'], 1
                );

                $question->setErrorMessage('Option %s is invalid.');
                $answer = $helper->ask($input, $output, $question);

                if ($answer == 'yes') {
                    rename($file, $folder.'/'.$fileName);
                    $viewFile = Str::truncate($file);
                    $console->writeln("file: <fg=yellow>{$viewFile}</> has been moved");
                } elseif ($answer == 'no') {
                    $console->writeln('<fg=yellow>file not moved</>');
                }
            }
        }

        if (count($notTracked) == 0) {
            $console->writeln('<fg=yellow>no files to move</>');
        }

        $event = $time->stop('move');
        $time = Str::humanTime($event->getDuration());
        $mb = Str::humanMemory($event->getMemory());
        $torrents = count($data['data-torrent']);
        $console->writeln(['', "> time: {$time}, torrents: {$torrents}, memory: {$mb}"]);
    }
}
