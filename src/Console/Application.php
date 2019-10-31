<?php

namespace Rtcleaner\Console;

use Rtcleaner\Command\DefaultCommand;
use Rtcleaner\Command\MoveCommand;
use Rtcleaner\Command\RemoveCommand;
use Rtcleaner\Command\ReportCommand;
use Rtcleaner\Command\TorrentsCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private $version = '0.9.4';
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
