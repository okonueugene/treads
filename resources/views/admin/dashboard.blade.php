@php
use App\Models\User;
$pendingVendors = User::query()->where('is_vendor', true)->where('is_vendor_approved', false)->count();
$totalVendors = User::query()->where('is_vendor', true)->where('is_vendor_approved', true)->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Admin Dashboard" subtitle="Manage your marketplace" class="mb-0" />
    </x-slot>

    <div class="page-container py-8 space-y-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-stat-card label="Pending Approvals" :value="$pendingVendors">
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot>
                <x-slot name="footer">
                    <a href="{{ route('admin.vendors.pending') }}" class="text-brand-400 hover:text-brand-300">Review pending vendors →</a>
                </x-slot>
            </x-stat-card>
            <x-stat-card label="Active Vendors" :value="$totalVendors">
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </x-slot>
            </x-stat-card>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('admin.vendors.pending') }}" class="card-hover p-6">
                <h3 class="font-semibold text-white">Vendor Management</h3>
                <p class="mt-1 text-sm text-slate-400">Approve pending vendor registrations</p>
            </a>
            <div class="card p-6 opacity-60">
                <h3 class="font-semibold text-white">Payouts & Reporting</h3>
                <p class="mt-1 text-sm text-slate-400">Coming soon</p>
            </div>
            <div class="card p-6 opacity-60">
                <h3 class="font-semibold text-white">Payments & Reconciliation</h3>
                <p class="mt-1 text-sm text-slate-400">Coming soon</p>
            </div>
        </div>
    </div>
</x-app-layout>
