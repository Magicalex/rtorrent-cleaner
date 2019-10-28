<?php

namespace Rtcleaner\Log;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Output
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

    public function table($header, $rows, $footer)
    {
        $console = (new Table($this->output))
            ->setHeaders($header)
            ->setRows($rows)
            ->addRow(new TableSeparator())
            ->addRow($footer)
            ->render();

        if ($this->log !== false) {
            $log = (new Table($this->log))->setHeaders($header)
                ->setRows($rows)
                ->addRow(new TableSeparator())
                ->addRow($footer)
                ->render();
        }
    }
}
