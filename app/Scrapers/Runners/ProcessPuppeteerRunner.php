<?php

namespace App\Scrapers\Runners;

use EduLazaro\Larascraper\Runners\PuppeteerRunner;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use JsonException;
use ReflectionClass;
use RuntimeException;
use Throwable;

class ProcessPuppeteerRunner extends PuppeteerRunner
{
    private const REQUIRED_NODE_PACKAGES = [
        'puppeteer',
        'puppeteer-extra',
        'puppeteer-extra-plugin-stealth',
    ];

    public function run(): array
    {
        $script = $this->scriptPath();
        $nodeBinary = $this->nodeBinary();

        $this->ensureNodeDependencies($nodeBinary, $script);

        try {
            $result = Process::path(base_path())
                ->timeout(max(10, (int) ceil($this->timeout / 1000) + 10))
                ->run(array_merge(
                    [$nodeBinary, $script],
                    $this->processArguments(),
                ));
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Larascraper could not start the Puppeteer process: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        if (! $result->successful()) {
            throw new RuntimeException($this->processFailureMessage(
                'Larascraper Puppeteer process failed',
                $result,
            ));
        }

        try {
            $payload = json_decode(trim($result->output()), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Larascraper Puppeteer process returned invalid JSON.',
                previous: $exception,
            );
        }

        if (! is_array($payload)) {
            throw new RuntimeException('Larascraper Puppeteer process returned an unexpected response.');
        }

        return [
            'success' => (bool) ($payload['success'] ?? false),
            'status' => (int) ($payload['status'] ?? 500),
            'html' => (string) ($payload['html'] ?? ''),
            'error' => isset($payload['error']) ? (string) $payload['error'] : null,
            'file' => isset($payload['file']) ? (string) $payload['file'] : null,
            'contentType' => isset($payload['contentType']) ? (string) $payload['contentType'] : null,
            'cookies' => is_array($payload['cookies'] ?? null) ? $payload['cookies'] : [],
            'diagnostics' => is_array($payload['diagnostics'] ?? null) ? $payload['diagnostics'] : [],
        ];
    }

    /** @return list<string> */
    private function processArguments(): array
    {
        $arguments = [
            '--url='.$this->url,
            '--timeout='.$this->timeout,
        ];

        if ($this->proxy) {
            $arguments[] = '--proxy='.$this->proxy;
        }

        if ($this->user) {
            $arguments[] = '--user='.$this->user;
        }

        if ($this->password) {
            $arguments[] = '--pass='.$this->password;
        }

        if ($this->userAgent) {
            $arguments[] = '--ua='.$this->userAgent;
        }

        if ($this->headers !== []) {
            $arguments[] = '--headers='.json_encode($this->headers, JSON_THROW_ON_ERROR);
        }

        if ($this->actions !== []) {
            $arguments[] = '--actions='.json_encode($this->actions, JSON_THROW_ON_ERROR);
        }

        return $arguments;
    }

    private function ensureNodeDependencies(string $nodeBinary, string $script): void
    {
        if (static::$dependenciesVerified) {
            return;
        }

        $packages = json_encode(self::REQUIRED_NODE_PACKAGES, JSON_THROW_ON_ERROR);
        $encodedScript = json_encode($script, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $javascript = 'const{createRequire}=require("module");'
            ."const r=createRequire({$encodedScript});"
            ."{$packages}.forEach(p=>r.resolve(p));";

        try {
            $result = Process::path(base_path())
                ->timeout(10)
                ->run([$nodeBinary, '-e', $javascript]);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "Larascraper could not run Node.js using [{$nodeBinary}]: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        if (! $result->successful()) {
            $installCommand = 'npm install '.implode(' ', self::REQUIRED_NODE_PACKAGES);

            throw new RuntimeException(
                $this->processFailureMessage('Larascraper Node dependencies are unavailable', $result)
                .". Run [{$installCommand}].",
            );
        }

        static::$dependenciesVerified = true;
    }

    private function nodeBinary(): string
    {
        $binary = trim((string) config('services.torob.node_binary', 'node'));

        return $binary !== '' ? $binary : 'node';
    }

    private function scriptPath(): string
    {
        $runnerFile = (new ReflectionClass(PuppeteerRunner::class))->getFileName();
        $script = dirname($runnerFile, 3).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'scraper.cjs';

        if (! is_file($script)) {
            throw new RuntimeException("Larascraper Puppeteer script was not found at [{$script}].");
        }

        return $script;
    }

    private function processFailureMessage(string $prefix, ProcessResult $result): string
    {
        $details = trim($result->errorOutput());

        if ($details === '') {
            $details = trim($result->output());
        }

        $details = $details !== '' ? mb_substr($details, 0, 500) : 'no error output';

        return "{$prefix} (exit {$result->exitCode()}): {$details}";
    }
}
