<?php

namespace Rtorrent\Cleaner\Console;

use Rtorrent\Cleaner\Command\DefaultCommand;
use Rtorrent\Cleaner\Command\MoveCommand;
use Rtorrent\Cleaner\Command\RemoveCommand;
use Rtorrent\Cleaner\Command\ReportCommand;
use Rtorrent\Cleaner\Command\TorrentsCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private $version = '0.9.0';
    private $name = 'rtorrent-cleaner';

    public function __construct()
    {
        parent::__construct($this->name, '<fg=white>version</> <comment>'.$this->version.'</>');
        $this->addCommands([
            new MoveCommand(),
            new ReportCommand(),
            new RemoveCommand(),
            new DefaultCommand(),
            new TorrentsCommand()
        ]);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        parent::run($input, $output);
    }
}
