<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app(\App\Settings\GeneralSettings::class)->title }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #18181b;
            color: #fafafa;
            font-family: Tahoma, sans-serif;
            text-align: center;
        }
        .box {
            max-width: 32rem;
            padding: 2rem;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        p {
            line-height: 2;
            color: #a1a1aa;
        }
        img {
            max-height: 4rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="box">
    @php
        $general = app(\App\Settings\GeneralSettings::class);
        $maintenance = app(\App\Settings\MaintenanceSettings::class);
    @endphp
    @if ($general->logo_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($general->logo_path) }}" alt="">
    @endif
    <h1>{{ $general->title }}</h1>
    <p>{{ $maintenance->message }}</p>
</div>
</body>
</html>
