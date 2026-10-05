<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{--
        No CDN scripts. All assets are built by Vite and versioned.

        @routes was removed: it is a Ziggy directive, Ziggy is not installed,
        and nothing in resources/js calls route() -- the navigation uses plain
        string paths. Laravel therefore printed the literal text "@routes" at the
        top of every page.
    --}}
    <title inertia>{{ config('app.name', 'RentFlow') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased">
    @inertia
</body>
</html>