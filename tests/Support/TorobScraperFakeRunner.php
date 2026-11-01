<?php

namespace Tests\Support;

use EduLazaro\Larascraper\Contracts\Runner;

class TorobScraperFakeRunner implements Runner
{
    /** @var list<array<string, mixed>> */
    public static array $responses = [];

    /** @var list<string> */
    public static array $urls = [];

    /** @var list<string> */
    public static array $proxies = [];

    /** @var array<string, string> */
    public static array $headers = [];

    private string $url;

    public static function reset(): void
    {
        self::$responses = [];
        self::$urls = [];
        self::$proxies = [];
        self::$headers = [];
    }

    public static function on(string $url): static
    {
        $runner = new static;
        $runner->url = $url;
        self::$urls[] = $url;

        return $runner;
    }

    public function authenticate(string $user, string $password): static
    {
        return $this;
    }

    public function proxy(string $proxy): static
    {
        self::$proxies[] = $proxy;

        return $this;
    }

    public function withHeaders(array $headers): static
    {
        self::$headers = $headers;

        return $this;
    }

    public function withActions(array $actions): static
    {
        return $this;
    }

    public function timeout(int $ms): static
    {
        return $this;
    }

    public function method(string $method): static
    {
        return $this;
    }

    public function body(mixed $body, string $format = 'form'): static
    {
        return $this;
    }

    public function cookies(array $cookies, ?string $domain = null): static
    {
        return $this;
    }

    public function supportsCookies(): bool
    {
        return false;
    }

    public function run(): array
    {
        return array_shift(self::$responses) ?? [
            'success' => false,
            'status' => 500,
            'html' => '',
            'error' => "No fake response configured for {$this->url}",
            'file' => null,
            'contentType' => 'text/html',
            'cookies' => [],
        ];
    }
}
