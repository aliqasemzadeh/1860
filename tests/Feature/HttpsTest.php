<?php

use Illuminate\Support\Facades\URL;

test('request with x-forwarded-proto https generates https canonical and scheme', function () {
    $response = $this->withHeaders([
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => '1860.ai',
    ])->get('/');

    $response->assertStatus(200);
    $response->assertSee('<link rel="canonical" href="https://1860.ai">', false);
    $response->assertSee('<meta property="og:url" content="https://1860.ai">', false);
});

test('url force scheme applies when app url is https', function () {
    URL::forceScheme('https');

    expect(url('/'))->toStartWith('https://');
    expect(route('home'))->toStartWith('https://');
});
