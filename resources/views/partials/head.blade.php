@php($general = app(\App\Settings\GeneralSettings::class))
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $title ?? $general->title }}</title>
@if (filled($description ?? $general->description))
    <meta name="description" content="{{ $description ?? $general->description }}" />
@endif
@if (filled($keywords ?? $general->keywords))
    <meta name="keywords" content="{{ $keywords ?? $general->keywords }}" />
@endif

@if ($general->favicon_path)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($general->favicon_path) }}" />
@else
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
