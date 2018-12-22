<?php

namespace RtorrentCleaner\Log;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Log
{
    protected $log;
    protected $output;
    protected $enabledLog;

    public function __construct(OutputInterface $output, $logFile)
    {
        $this->output = $output;
        $this->enabledLog = ($logFile === false) ? false : true;

        if ($this->enabledLog === true) {
            $this->log = new StreamOutput(fopen($logFile, 'w+'));
        }
    }

    public function writeln($data)
    {
        $this->output->writeln($data);

        if ($this->enabledLog === true) {
            $this->log->writeln($data);
        }
    }

    public function table($header, $data)
    {
        $console = new Table($this->output);
        $console->setHeaders($header)->setRows($data);
        $console->render();

        if ($this->enabledLog === true) {
            $log = new Table($this->log);
            $log->setHeaders($header)->setRows($data);
            $log->render();
        }
    }
}
