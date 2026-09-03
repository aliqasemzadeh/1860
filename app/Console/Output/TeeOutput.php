<?php

namespace App\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TeeOutput extends Output
{
    private string $buffer = '';

    private ?self $stderrTee = null;

    public function __construct(private readonly OutputInterface $inner)
    {
        parent::__construct($inner->getVerbosity(), $inner->isDecorated(), $inner->getFormatter());
    }

    public static function wrap(OutputInterface $output): self
    {
        if ($output instanceof ConsoleOutputInterface) {
            $stderrTee = new self($output->getErrorOutput());
            $output->setErrorOutput($stderrTee);

            $stdoutTee = new self($output);
            $stdoutTee->stderrTee = $stderrTee;

            return $stdoutTee;
        }

        return new self($output);
    }

    public function fetch(): string
    {
        $output = $this->buffer;

        if ($this->stderrTee !== null) {
            $output .= $this->stderrTee->fetch();
        }

        return $output;
    }

    public function setVerbosity(int $level): void
    {
        parent::setVerbosity($level);
        $this->inner->setVerbosity($level);
        $this->stderrTee?->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->inner->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->inner->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->inner->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->inner->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->inner->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        parent::setDecorated($decorated);
        $this->inner->setDecorated($decorated);
        $this->stderrTee?->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->inner->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        parent::setFormatter($formatter);
        $this->inner->setFormatter($formatter);
        $this->stderrTee?->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->inner->getFormatter();
    }

    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message.($newline ? PHP_EOL : '');
        $this->inner->write($message, $newline, self::OUTPUT_RAW);
    }

    public function section(): ConsoleSectionOutput
    {
        if ($this->inner instanceof ConsoleOutputInterface) {
            return $this->inner->section();
        }

        throw new \RuntimeException('Output does not support sections.');
    }

    public function getErrorOutput(): OutputInterface
    {
        if ($this->inner instanceof ConsoleOutputInterface) {
            return $this->inner->getErrorOutput();
        }

        return $this->inner;
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        if ($this->inner instanceof ConsoleOutputInterface) {
            $this->inner->setErrorOutput($error);
        }
    }
}
