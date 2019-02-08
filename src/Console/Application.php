<?php

namespace Rtorrent\Cleaner\Console;

use Symfony\Component\Console\Application as App;
use Rtorrent\Cleaner\Command\TorrentsCommand;
use Rtorrent\Cleaner\Command\MoveCommand;
use Rtorrent\Cleaner\Command\RemoveCommand;
use Rtorrent\Cleaner\Command\ReportCommand;

class Application
{
    private $app;
    private $version = '0.5.2';
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
        $this->app->add(new ReportCommand());
        $this->app->add(new RemoveCommand());
        $this->app->add(new MoveCommand());
        $this->app->add(new TorrentsCommand());
        $this->app->run();
    }

}
