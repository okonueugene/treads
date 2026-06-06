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
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen bg-surface">
            {{-- Brand panel --}}
            <div class="relative hidden w-1/2 overflow-hidden lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-gradient-to-br from-black via-surface-card to-brand-900/50"></div>
                <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 30% 50%, rgba(0,174,239,0.35) 0%, transparent 55%);"></div>
                <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(circle at 80% 80%, rgba(247,148,29,0.25) 0%, transparent 45%);"></div>
                <div class="relative z-10 flex flex-col justify-between p-12">
                    <a href="{{ route('home') }}" class="inline-block">
                        <x-brand-logo variant="full" class="max-h-20" />
                    </a>
                    <div class="max-w-md animate-fade-in">
                        <h1 class="text-4xl font-bold leading-tight text-white">
                            Find the perfect tires for your ride
                        </h1>
                        <p class="mt-4 text-lg text-surface-silver">
                            Shop thousands of tires from trusted vendors. Use our fitment checker to match your vehicle instantly.
                        </p>
                        <div class="mt-8 flex gap-4">
                            <a href="{{ route('shop.index') }}" class="btn-primary">Browse Shop</a>
                            <a href="{{ route('fitment.index') }}" class="btn-secondary">Check Fitment</a>
                        </div>
                    </div>
                    <p class="text-sm text-surface-silver/70">&copy; {{ date('Y') }} {{ config('app.name', 'The Digital Tread') }}</p>
                </div>
            </div>

            {{-- Form panel --}}
            <div class="flex w-full flex-col justify-center bg-black px-6 py-12 lg:w-1/2 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <a href="{{ route('home') }}" class="inline-block">
                            <x-brand-logo variant="mark" />
                        </a>
                    </div>

                    <div class="card p-8 animate-slide-up">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
