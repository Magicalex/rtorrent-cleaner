<?php

namespace Rtorrent\Cleaner\Log;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Log
{
    protected $output;
    protected $log = false;

    public function __construct(OutputInterface $output, $log)
    {
        $this->output = $output;

        if ($log === null) {
            $this->log = new StreamOutput(fopen('rtorrent-cleaner.log', 'a'));
        } elseif ($log !== false) {
            $this->log = new StreamOutput(fopen($log, 'a'));
        }
    }

    public function writeln($data)
    {
        $this->output->writeln($data);

        if ($this->log !== false) {
            $this->log->writeln($data);
        }
    }

    public function table($header, $data)
    {
        $console = new Table($this->output);
        if (version_compare(PHP_VERSION, '7.1.3', '>=')) {
            $console->setStyle('box');
        }
        $console->setHeaders($header)->setRows($data);
        $console->render();

        if ($this->log !== false) {
            $log = new Table($this->log);
            $log->setStyle('box');
            $log->setHeaders($header)->setRows($data);
            $log->render();
        }
    }
}
