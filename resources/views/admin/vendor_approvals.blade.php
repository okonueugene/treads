<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Vendor Approvals" subtitle="Review and approve pending vendor registrations" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        @if ($vendors->isEmpty())
            <x-empty-state title="No pending vendors" description="All vendor registrations have been processed." />
        @else
            <div class="space-y-4">
                @foreach ($vendors as $v)
                    <div class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-white">{{ $v->name }}</h3>
                            <p class="text-sm text-slate-400">{{ $v->email }}</p>
                            <p class="mt-1 text-sm text-brand-400">{{ $v->shop_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">Registered {{ $v->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.vendors.approve', $v) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Approve</button>
                            </form>

                            <form method="POST" action="{{ route('admin.vendors.reject', $v) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="text" name="reason" placeholder="Rejection reason (optional)" class="text-sm rounded bg-slate-700 px-2 py-1 text-slate-300" />
                                <button type="submit" class="btn-danger">Reject</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
