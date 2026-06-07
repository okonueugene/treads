<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'The Digital Tread') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        {{-- SweetAlert2 --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col bg-surface">
            @include('layouts.navigation')

            @isset($hero)
                {{ $hero }}
            @endisset

            @isset($header)
                <header class="border-b border-surface-border/60 bg-surface-card/40">
                    <div class="page-container py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1" style="view-transition-name: main-content">
                @if (session('status'))
                    <div class="page-container pt-6">
                        <x-alert type="success">{{ session('status') }}</x-alert>
                    </div>
                @endif

                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>
        @livewireScripts
    </body>
</html>
