User-agent: *
Allow: /
@foreach (config('seo.robots.disallow', []) as $path)
Disallow: {{ $path }}
@endforeach

Sitemap: {{ route('sitemap') }}
