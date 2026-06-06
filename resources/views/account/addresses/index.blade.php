<x-account-layout title="Addresses" subtitle="Saved delivery locations for faster checkout.">
  @if (session('status'))
    <div class="mb-4 rounded-lg border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-300">{{ session('status') }}</div>
  @endif

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="card p-6">
      <h2 class="mb-4 text-lg font-semibold text-white">Add address</h2>
      <form method="POST" action="{{ route('account.addresses.store') }}" class="space-y-4">
        @csrf
        <div>
          <x-input-label for="label" value="Label" />
          <x-text-input id="label" name="label" class="mt-1 block w-full" value="{{ old('label', 'Home') }}" required />
        </div>
        <div>
          <x-input-label for="name" value="Full name" />
          <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', auth()->user()->name)" required />
        </div>
        <div>
          <x-input-label for="phone" value="Phone" />
          <x-text-input id="phone" name="phone" class="mt-1 block w-full" value="{{ old('phone') }}" required />
        </div>
        <div>
          <x-input-label for="county" value="County" />
          <select id="county" name="county" class="input-field mt-1 block w-full" required>
            <option value="">Select county</option>
            @foreach ($counties as $county)
              <option value="{{ $county }}" @selected(old('county') === $county)>{{ $county }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <x-input-label for="town" value="Town" />
          <x-text-input id="town" name="town" class="mt-1 block w-full" value="{{ old('town') }}" required />
        </div>
        <div>
          <x-input-label for="address" value="Address" />
          <x-text-input id="address" name="address" class="mt-1 block w-full" value="{{ old('address') }}" required />
        </div>
        <div>
          <x-input-label for="landmark" value="Landmark" />
          <x-text-input id="landmark" name="landmark" class="mt-1 block w-full" value="{{ old('landmark') }}" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-300">
          <input type="checkbox" name="is_default" value="1" class="rounded border-slate-600 bg-slate-800 text-brand-500">
          Set as default
        </label>
        <x-primary-button>Save address</x-primary-button>
      </form>
    </div>

    <div class="space-y-4">
      @forelse ($addresses as $address)
        <div class="card p-5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-white">{{ $address->label }} @if($address->is_default)<span class="text-xs text-brand-400">(Default)</span>@endif</p>
              <p class="mt-1 text-sm text-slate-300">{{ $address->name }} · {{ $address->phone }}</p>
              <p class="mt-2 text-sm text-slate-400">{{ $address->formatted() }}</p>
            </div>
            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-sm text-red-400 hover:text-red-300">Remove</button>
            </form>
          </div>
        </div>
      @empty
        <x-empty-state title="No saved addresses" description="Add an address to speed up checkout." />
      @endforelse
    </div>
  </div>
</x-account-layout>
