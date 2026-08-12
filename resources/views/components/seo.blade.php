@props([
    'seo' => null,
    'title' => null,
    'noindex' => null,
])

@php
    use App\Support\Seo\Seo;

    /** @var Seo $seo */
    $seo ??= Seo::site(title: $title, noindex: (bool) ($noindex ?? false));
    $resolvedTitle = $title ?: $seo->title;
    $resolvedNoindex = $noindex ?? ($seo->noindex || Seo::shouldNoindex(request()));
    $siteName = app(\App\Settings\GeneralSettings::class)->title ?: config('app.name');
@endphp

<title>{{ $resolvedTitle }}</title>

@if (filled($seo->description))
    <meta name="description" content="{{ $seo->description }}">
@endif

@if (filled($seo->keywords))
    <meta name="keywords" content="{{ $seo->keywords }}">
@endif

@if (filled($seo->canonical))
    <link rel="canonical" href="{{ $seo->canonical }}">
@endif

@if ($resolvedNoindex)
    <meta name="robots" content="noindex, follow">
@endif

@if (filled($seo->prev))
    <link rel="prev" href="{{ $seo->prev }}">
@endif

@if (filled($seo->next))
    <link rel="next" href="{{ $seo->next }}">
@endif

<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
@if (filled($seo->description))
    <meta property="og:description" content="{{ $seo->description }}">
@endif
@if (filled($seo->canonical))
    <meta property="og:url" content="{{ $seo->canonical }}">
@endif
@if (filled($seo->image))
    <meta property="og:image" content="{{ $seo->image }}">
    <meta property="og:image:alt" content="{{ $resolvedTitle }}">
@endif
<meta property="og:locale" content="{{ config('seo.locale', 'fa_IR') }}">
<meta property="og:site_name" content="{{ $siteName }}">

<meta name="twitter:card" content="{{ filled($seo->image) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
@if (filled($seo->description))
    <meta name="twitter:description" content="{{ $seo->description }}">
@endif
@if (filled($seo->image))
    <meta name="twitter:image" content="{{ $seo->image }}">
@endif

@foreach ($seo->meta as $name => $content)
    @if (filled($content))
        <meta name="{{ $name }}" content="{{ $content }}">
    @endif
@endforeach

@foreach ($seo->schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
