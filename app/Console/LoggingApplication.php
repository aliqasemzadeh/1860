<?php

namespace App\Console;

use App\Console\Output\TeeOutput;
use App\Support\CommandLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class LoggingApplication extends \Illuminate\Console\Application
{
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (CommandLogger::shouldSkip($command)) {
            return parent::doRunCommand($command, $input, $output);
        }

        $startedAt = microtime(true);
        $commandName = $command->getName() ?? $command::class;
        $tee = TeeOutput::wrap($output);

        CommandLogger::started($commandName, $input);

        try {
            $exitCode = parent::doRunCommand($command, $input, $tee);
        } catch (Throwable $exception) {
            CommandLogger::failed($commandName, $input, $tee->fetch(), $startedAt, $exception);

            throw $exception;
        }

        CommandLogger::finished($commandName, $input, $tee->fetch(), $exitCode, $startedAt);

        return $exitCode;
    }
}
