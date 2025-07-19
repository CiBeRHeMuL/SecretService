<?php

namespace App\Presentation\Console\Listener;

use Perfluence\SymfonyShared\Domain\Exception\ValidationException;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: ConsoleErrorEvent::class, priority: 10)]
class ValidationListener
{
    public function __invoke(ConsoleErrorEvent $event): void
    {
        $e = $event->getError();
        if ($e instanceof ValidationException) {
            $headers = [];
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $headers[] = $error->getField();
                $errors[] = $error->getMessage();
            }
            $this->printTable(
                $event->getInput(),
                $event->getOutput(),
                $headers,
                [$errors],
            );
            $event->stopPropagation();
        } elseif ($e instanceof ValidationFailedException) {
            $headers = [];
            $errors = [];
            foreach ($e->getViolations() as $violation) {
                $headers[] = $violation->getPropertyPath();
                $errors[] = $violation->getMessage();
            }
            $this->printTable(
                $event->getInput(),
                $event->getOutput(),
                $headers,
                [$errors],
            );
            $event->stopPropagation();
        }
    }

    private function printTable(InputInterface $input, OutputInterface $output, array $headers, array $errors): void
    {
        $output = $output instanceof ConsoleOutputInterface ? $output->section() : $output;
        $io = new SymfonyStyle($input, $output);
        $table = $io
            ->createTable()
            ->setHorizontal()
            ->setHeaders($headers)
            ->setRows($errors);
        $table->getStyle()->setCellHeaderFormat('<fg=red>%s</>');
        $table->render();
        $io->newLine();
    }
}
