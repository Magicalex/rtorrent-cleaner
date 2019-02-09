<?php

namespace Rtorrent\Cleaner\Console;

use Rtorrent\Cleaner\Command\MoveCommand;
use Rtorrent\Cleaner\Command\RemoveCommand;
use Rtorrent\Cleaner\Command\ReportCommand;
use Rtorrent\Cleaner\Command\TorrentsCommand;
use Symfony\Component\Console\Application as App;

class Application
{
    private $app;
    private $version = '0.6.0';
    private $logo = "      _                            _          _
 _ __| |_ ___  _ __ _ __ ___ _ __ | |_    ___| | ___  __ _ _ __   ___ _ __
| '__| __/ _ \| '__| '__/ _ \ '_ \| __|  / __| |/ _ \/ _` | '_ \ / _ \ '__|
| |  | || (_) | |  | | |  __/ | | | |_  | (__| |  __/ (_| | | | |  __/ |
|_|   \__\___/|_|  |_|  \___|_| |_|\__|  \___|_|\___|\__,_|_| |_|\___|_|\n";

    public function __construct()
    {
        $this->app = new App();
        $this->app->setName($this->logo);
        $this->app->setVersion("\n<fg=white>rtorrent-cleaner version</> ".$this->version);
    }

    public function run()
    {
        $this->app->addCommands([
            new MoveCommand(),
            new ReportCommand(),
            new RemoveCommand(),
            new TorrentsCommand()
        ]);

        $this->app->run();
    }
}
