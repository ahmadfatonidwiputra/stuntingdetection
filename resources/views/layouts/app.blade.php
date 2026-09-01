<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AI Stunt Detect') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ versioned_asset('logo.png') }}">

        <!-- Open Graph / WhatsApp Meta Tags -->
        <meta property="og:title" content="{{ config('app.name', 'AI Stunt Detect') }}" />
        <meta property="og:description" content="AI Stunt Detect - Sistem deteksi dini stunting berbasis kecerdasan buatan" />
        <meta property="og:image" content="{{ versioned_asset('logo.png') }}" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{{ config('app.name', 'AI Stunt Detect') }}" />        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
