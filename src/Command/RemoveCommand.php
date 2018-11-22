<?php

namespace RtorrentCleaner\Command;

use RtorrentCleaner\Utils\ListingFile;
use RtorrentCleaner\Utils\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RemoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rm')
            ->setDescription('delete unnecessary files')
            ->setHelp('Command rm for delete unnecessary files in your download folder')
            ->addOption(
                'url-xmlrcp',
                null,
                InputOption::VALUE_REQUIRED,
                'set url to your scgi mount point like: http://user:pass@localhost:80/RCP',
                'http://localhost:80/RCP')
            ->addOption(
                'home',
                null,
                InputOption::VALUE_REQUIRED,
                'set folder of your home like: /home/user/torrents',
                '/data'
            )
            ->addOption(
                'assume-yes',
                null,
                InputOption::VALUE_NONE,
                'Delete all the files without confirmation'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '============================',
            '= <fg=red>REMOVE UNNECESSARY FILES</> =',
            '============================',
            '',
            ' -> <fg=red>Retrieving the list of concerned files.</>',
            ''
        ]);

        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrcp'));
        $data_rtorrent = $list->listing_from_rtorrent($output);
        $data_home = $list->listing_from_home();
        $notTracked = $list->getFilesNotTracked($data_home, $data_rtorrent['path']);

        // remove files not tracked
        foreach ($notTracked as $file) {
            $trunc = Str::truncate($file, 70);

            if ($input->getOption('assume-yes') === true) {
                unlink($file);
                $output->writeln("file: <fg=red>{$trunc}</> has been removed");
            } elseif ($input->getOption('assume-yes') === false) {
                $helper = $this->getHelper('question');
                $question = new Question("Are you sure you want to delete the <fg=red>{$trunc}</> file? [y|n] ", 'n');

                if ($helper->ask($input, $output, $question) === 'y') {
                    unlink($file);
                    $output->writeln("file: <fg=red>{$trunc}</> has been removed.");
                } else {
                    $output->writeln('file not deleted.');
                }
            }
        }

        // remove empty directory
        $emptyDirectory = $list->getEmptyDirectory();

        if (count($emptyDirectory) == 0) {
            $output->writeln('no empty directory');
        } else {
            while (count($emptyDirectory) > 0) {
                $list->removeEmptyDirectory($emptyDirectory, $output);
                $emptyDirectory = $list->getEmptyDirectory();
            }
        }
    }
}
