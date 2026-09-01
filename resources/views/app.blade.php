<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="Tabarak Trading wholesale food catalogue for retailers and food-service partners.">
        <meta name="theme-color" content="#4058E1">
        <meta name="application-name" content="Tabarak Trading">
        <meta name="apple-mobile-web-app-title" content="Tabarak Trading">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <title inertia>{{ config('app.name', 'Tabarak Trading V2') }}</title>

        <!-- Brand icons -->
        <link rel="icon" type="image/svg+xml" href="/icons/tabarak-icon.svg">
        <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
