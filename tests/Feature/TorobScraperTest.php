<?php

use App\Scrapers\TorobScraper;
use App\Support\TorobChallengeException;
use App\Support\TorobChallengeGuard;
use EduLazaro\Larascraper\Contracts\Runner;
use EduLazaro\Larascraper\Exceptions\RequestException;
use Illuminate\Support\Facades\Cache;

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

function torobBrowserResponse(array|string $payload, int $status = 200): array
{
    $json = is_array($payload) ? json_encode($payload, JSON_THROW_ON_ERROR) : $payload;

    return [
        'success' => $status >= 200 && $status < 300,
        'status' => $status,
        'html' => '<html><body><pre>'.htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</pre></body></html>',
        'error' => $status >= 400 ? "HTTP {$status}" : null,
        'file' => null,
        'contentType' => 'text/html',
        'cookies' => [],
    ];
}

function runTorobScraper(string $productKey = 'ad77d6f4-d0de-4ec9-9572-a05fbd27ad70')
{
    $throttle = config('larascraper.throttle', []);
    $throttle['torob.sellers']['interval'] = 0;
    config()->set('larascraper.throttle', $throttle);

    $scraper = app(TorobScraper::class);
    $scraper->applyWith([
        'drivers' => ['browser' => TorobScraperFakeRunner::class],
        'tries' => 1,
    ]);

    return $scraper->handleToResponse([$productKey]);
}

beforeEach(function () {
    TorobScraperFakeRunner::reset();
    Cache::flush();
});

test('torob scraper extracts browser JSON and follows seller pagination without a proxy', function () {
    TorobScraperFakeRunner::$responses = [
        torobBrowserResponse([
            'count' => 101,
            'results' => [[
                'availability' => true,
                'shop_name' => 'فروشگاه صفحه اول',
                'price' => 20_000_000,
            ]],
        ]),
        torobBrowserResponse([
            'count' => 101,
            'results' => [[
                'availability' => true,
                'shop_name' => 'فروشگاه صفحه دوم',
                'price' => 19_000_000,
            ]],
        ]),
    ];

    $response = runTorobScraper();

    expect($response->success)->toBeTrue()
        ->and($response->data)->toHaveCount(2)
        ->and($response->data[1]['shop_name'])->toBe('فروشگاه صفحه دوم')
        ->and(TorobScraperFakeRunner::$urls)->toHaveCount(2)
        ->and(TorobScraperFakeRunner::$urls[0])->toContain('page=0', 'size=100')
        ->and(TorobScraperFakeRunner::$urls[1])->toContain('page=1')
        ->and(TorobScraperFakeRunner::$proxies)->toBe(['', ''])
        ->and(TorobScraperFakeRunner::$headers)->toMatchArray([
            'Accept' => 'application/json',
            'Origin' => 'https://torob.com',
            'Referer' => 'https://torob.com/',
        ]);
});

test('torob scraper rejects invalid JSON returned by the browser', function () {
    TorobScraperFakeRunner::$responses = [torobBrowserResponse('not-json')];

    expect(fn () => runTorobScraper())
        ->toThrow(RuntimeException::class, 'did not contain valid JSON');
});

test('torob scraper detects ARCaptcha HTML and activates a shared cooldown', function () {
    config()->set('services.torob.challenge_cooldown_seconds', 3600);
    $retryAt = now()->addHour()->timestamp;
    TorobScraperFakeRunner::$responses = [[
        'success' => true,
        'status' => 200,
        'html' => '<html><head><title>ARCaptcha</title></head><body><h1>آیا شما یک ربات هستید؟</h1><p>من ربات نیستم</p></body></html>',
        'error' => null,
        'file' => null,
        'contentType' => 'text/html',
        'cookies' => [],
    ]];

    expect(fn () => runTorobScraper())
        ->toThrow(TorobChallengeException::class, 'ARCaptcha')
        ->and((int) Cache::get(TorobChallengeGuard::CACHE_KEY))->toBe($retryAt)
        ->and(TorobScraperFakeRunner::$urls)->toHaveCount(1);
});

test('torob scraper makes no outbound request while the challenge cooldown is active', function () {
    Cache::put(TorobChallengeGuard::CACHE_KEY, now()->addHour()->timestamp, 3600);
    TorobScraperFakeRunner::$responses = [torobBrowserResponse([
        'count' => 0,
        'results' => [],
    ])];

    expect(fn () => runTorobScraper())
        ->toThrow(TorobChallengeException::class, 'Requests are paused')
        ->and(TorobScraperFakeRunner::$urls)->toBeEmpty();
});

test('torob scraper resumes requests after the challenge cooldown expires', function () {
    Cache::put(TorobChallengeGuard::CACHE_KEY, now()->subSecond()->timestamp, 3600);
    TorobScraperFakeRunner::$responses = [torobBrowserResponse([
        'count' => 0,
        'results' => [],
    ])];

    $response = runTorobScraper();

    expect($response->success)->toBeTrue()
        ->and($response->data)->toBe([])
        ->and(Cache::has(TorobChallengeGuard::CACHE_KEY))->toBeFalse()
        ->and(TorobScraperFakeRunner::$urls)->toHaveCount(1);
});

test('torob seller requests have a shared fifteen second throttle by default', function () {
    $throttle = config('larascraper.throttle', []);

    expect(config('larascraper.proxies'))->toBe([])
        ->and($throttle['torob.sellers']['interval'] ?? null)->toBe(15);
});

test('torob scraper rejects an unexpected sellers response', function () {
    TorobScraperFakeRunner::$responses = [torobBrowserResponse(['count' => 1])];

    expect(fn () => runTorobScraper())
        ->toThrow(RuntimeException::class, 'unexpected structure');
});

test('torob scraper exposes an unsuccessful browser response', function () {
    TorobScraperFakeRunner::$responses = [torobBrowserResponse([], 490)];

    try {
        runTorobScraper();
        $this->fail('Expected the Torob request to fail.');
    } catch (RequestException $exception) {
        expect($exception->response->status)->toBe(490);
    }
});
