<?php

namespace Rtorrent\Cleaner\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private $version = '0.6.1';
    private $name = 'rtorrent-cleaner';

    public function __construct()
    {
        parent::__construct($this->name, '<fg=white>version</> <comment>'.$this->version.'</>');
        $this->addCommands([
            new \Rtorrent\Cleaner\Command\MoveCommand(),
            new \Rtorrent\Cleaner\Command\ReportCommand(),
            new \Rtorrent\Cleaner\Command\RemoveCommand(),
            new \Rtorrent\Cleaner\Command\DefaultCommand(),
            new \Rtorrent\Cleaner\Command\TorrentsCommand()
        ]);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, $output);
    }
}
