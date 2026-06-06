<nav class="card space-y-1 p-3">
    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">My Account</p>
    @foreach ([
        ['route' => 'account.index', 'label' => 'Overview', 'pattern' => 'account.index'],
        ['route' => 'account.orders.index', 'label' => 'Orders', 'pattern' => 'account.orders.*'],
        ['route' => 'account.addresses.index', 'label' => 'Addresses', 'pattern' => 'account.addresses.*'],
        ['route' => 'account.reviews.index', 'label' => 'Reviews', 'pattern' => 'account.reviews.*'],
        ['route' => 'account.tire-requests.index', 'label' => 'Tire Requests', 'pattern' => 'account.tire-requests.*'],
        ['route' => 'profile.edit', 'label' => 'Profile', 'pattern' => 'profile.*'],
    ] as $link)
        <a
            href="{{ route($link['route']) }}"
            class="block rounded-lg px-3 py-2 text-sm transition {{ request()->routeIs($link['pattern']) ? 'bg-brand-500/10 font-medium text-brand-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
