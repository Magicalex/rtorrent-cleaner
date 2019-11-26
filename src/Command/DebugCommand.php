<?php

namespace Rtcleaner\Command;

use Rtcleaner\Debug;
use Rtcleaner\Helpers;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class DebugCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('debug')
            ->setDescription('Debug torrents')
            ->setHelp('Command debug for collect some informations about a torrent')
            ->addArgument(
                'scgi',
                InputArgument::REQUIRED,
                'Set the scgi hostname:port or socket file of rtorrent')
            ->addArgument(
                'hash',
                InputArgument::REQUIRED,
                'Set the hash of the torrent');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = (new Stopwatch())->start('torrent');
        Helpers::title('rtorrent-cleaner - debug', $output);
        $scgi = Helpers::scgiArgument($input->getArgument('scgi'));
        $debug = new Debug($scgi['hostname'], $scgi['port'], $input->getArgument('hash'));

        $rows = [];
        $output->writeln(['', '> Downloads infos', '']);
        $table = new Table($output);
        $table->setHeaders(['command: d.*', 'result']);
        foreach ($debug->getTorrentInfo() as $cmd => $result) {
            $rows[] = [$cmd, Helpers::truncate($result, 80)];
        }
        $table->setRows($rows);
        $table->render();

        $output->writeln(['', '> Files infos']);

        foreach ($debug->getFilesInfo() as $file) {
            $rows = [];
            $output->writeln('');
            $table = new Table($output);
            $table->setHeaders(['command: f.*', 'result']);
            foreach ($file as $cmd => $result) {
                $rows[] = [$cmd, Helpers::truncate($result, 80)];
            }
            $table->setRows($rows);
            $table->render();
        }

        $date = (new \DateTime())->format('D, j M Y H:i:s');
        $event = $time->stop();
        $time = Helpers::humanTime($event->getDuration());
        $output->writeln(['', '> time: '.$time.', date: '.$date]);
    }
}
