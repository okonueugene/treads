@props(['title', 'subtitle' => null])

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$title" :subtitle="$subtitle" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        {{-- Mobile: horizontal scrollable tab nav --}}
        <nav class="mb-6 -mx-4 overflow-x-auto px-4 lg:hidden">
            <div class="flex min-w-max gap-1 border-b border-slate-800 pb-0">
                @foreach ([
                    ['route' => 'account.index',           'label' => 'Overview',      'pattern' => 'account.index'],
                    ['route' => 'account.orders.index',    'label' => 'Orders',        'pattern' => 'account.orders.*'],
                    ['route' => 'account.addresses.index', 'label' => 'Addresses',     'pattern' => 'account.addresses.*'],
                    ['route' => 'account.reviews.index',   'label' => 'Reviews',       'pattern' => 'account.reviews.*'],
                    ['route' => 'account.tire-requests.index', 'label' => 'Tire Requests', 'pattern' => 'account.tire-requests.*'],
                    ['route' => 'profile.edit',            'label' => 'Profile',       'pattern' => 'profile.*'],
                ] as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition {{ request()->routeIs($link['pattern']) ? 'border-brand-500 text-brand-400' : 'border-transparent text-slate-400 hover:text-white' }}"
                    >{{ $link['label'] }}</a>
                @endforeach
            </div>
        </nav>

        {{-- Desktop: sidebar + content --}}
        <div class="grid gap-8 lg:grid-cols-4">
            <aside class="hidden lg:block lg:col-span-1">
                @include('account.partials.nav')
            </aside>
            <div class="lg:col-span-3">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
