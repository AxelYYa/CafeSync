@props(['titulo' => null, 'claseCuerpo' => ''])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>{{ $titulo ? $titulo.' · ' : '' }}CaféSync</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%23352419'/%3E%3Cpath d='M7 9h8v4a3 3 0 0 1-3 3h-2a3 3 0 0 1-3-3Zm8 1h1a1.5 1.5 0 0 1 0 3h-1' fill='none' stroke='%23e9a24f' stroke-width='1.6'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->class(['min-h-dvh', $claseCuerpo]) }}>
    {{ $slot }}
</body>
</html>
