<?php

namespace App\Presentation\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('test:error')]
class TestErrorCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new RuntimeException('Test error');
    }
}
