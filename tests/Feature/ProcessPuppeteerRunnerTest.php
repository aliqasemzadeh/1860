<?php

use App\Scrapers\Runners\ProcessPuppeteerRunner;
use EduLazaro\Larascraper\Runners\PuppeteerRunner;
use Illuminate\Support\Facades\Process;

function resetPuppeteerDependencyCheck(): void
{
    $property = new ReflectionProperty(PuppeteerRunner::class, 'dependenciesVerified');
    $property->setValue(null, false);
}

beforeEach(function () {
    resetPuppeteerDependencyCheck();
    config()->set('services.torob.node_binary', 'C:\Program Files\nodejs\node.exe');
});

test('portable puppeteer runner executes Node with array arguments on Windows', function () {
    Process::fake(function ($process) {
        $command = $process->command;

        if (is_array($command) && ($command[1] ?? null) === '-e') {
            return Process::result();
        }

        return Process::result(json_encode([
            'success' => true,
            'status' => 200,
            'html' => '<html><body><pre>{}</pre></body></html>',
            'cookies' => ['session' => 'value'],
            'diagnostics' => ['xhr' => ['request completed']],
        ], JSON_THROW_ON_ERROR));
    });

    $result = ProcessPuppeteerRunner::on('https://api.torob.com/sellers/?page=0')
        ->withHeaders([
            'Accept' => 'application/json',
            'Referer' => 'https://torob.com/',
        ])
        ->timeout(12_000)
        ->run();

    expect($result)->toMatchArray([
        'success' => true,
        'status' => 200,
        'html' => '<html><body><pre>{}</pre></body></html>',
        'cookies' => ['session' => 'value'],
    ]);

    Process::assertRan(function ($process): bool {
        $command = $process->command;

        return is_array($command)
            && $command[0] === 'C:\Program Files\nodejs\node.exe'
            && $command[1] === '-e'
            && str_contains($command[2], 'require("module")')
            && str_contains($command[2], 'puppeteer-extra-plugin-stealth');
    });

    Process::assertRan(function ($process): bool {
        $command = $process->command;
        $headers = collect($command)
            ->first(fn ($argument): bool => is_string($argument) && str_starts_with($argument, '--headers='));

        return is_array($command)
            && $command[0] === 'C:\Program Files\nodejs\node.exe'
            && str_ends_with($command[1], 'resources'.DIRECTORY_SEPARATOR.'scraper.cjs')
            && in_array('--url=https://api.torob.com/sellers/?page=0', $command, true)
            && in_array('--timeout=12000', $command, true)
            && is_string($headers)
            && json_decode(substr($headers, strlen('--headers=')), true) === [
                'Accept' => 'application/json',
                'Referer' => 'https://torob.com/',
            ];
    });
});

test('portable puppeteer runner reports unavailable Node dependencies', function () {
    Process::fake(fn () => Process::result(
        errorOutput: 'Cannot find module puppeteer',
        exitCode: 1,
    ));

    expect(fn () => ProcessPuppeteerRunner::on('https://torob.com')->run())
        ->toThrow(RuntimeException::class, 'Node dependencies are unavailable');
});

test('portable puppeteer runner reports a failed browser process', function () {
    Process::fake(function ($process) {
        if (is_array($process->command) && ($process->command[1] ?? null) === '-e') {
            return Process::result();
        }

        return Process::result(errorOutput: 'Chrome failed to launch', exitCode: 1);
    });

    expect(fn () => ProcessPuppeteerRunner::on('https://torob.com')->run())
        ->toThrow(RuntimeException::class, 'Puppeteer process failed');
});

test('portable puppeteer runner rejects invalid process JSON', function () {
    Process::fake(function ($process) {
        if (is_array($process->command) && ($process->command[1] ?? null) === '-e') {
            return Process::result();
        }

        return Process::result('not-json');
    });

    expect(fn () => ProcessPuppeteerRunner::on('https://torob.com')->run())
        ->toThrow(RuntimeException::class, 'returned invalid JSON');
});
