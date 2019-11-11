<?php

namespace Rtcleaner\Command;

use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultCommand extends ListCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '      _                            _          _',
            ' _ __| |_ ___  _ __ _ __ ___ _ __ | |_    ___| | ___  __ _ _ __   ___ _ __',
            '| \'__| __/ _ \| \'__| \'__/ _ \ \'_ \| __|  / __| |/ _ \/ _` | \'_ \ / _ \ \'__|',
            '| |  | || (_) | |  | | |  __/ | | | |_  | (__| |  __/ (_| | | | |  __/ |',
            '|_|   \__\___/|_|  |_|  \___|_| |_|\__|  \___|_|\___|\__,_|_| |_|\___|_|'
        ]);

        parent::execute($input, $output);
    }
}
