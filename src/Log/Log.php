<?php

namespace RtorrentCleaner\Log;

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
        $this->enabledLog = (isset($logFile)) ? true : false;

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
}
