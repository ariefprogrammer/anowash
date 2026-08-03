<?php

namespace App\Livewire\App\Outlets;

use App\Models\Outlet;
use App\Models\Subscription;
use App\Models\SubscriptionPricingTier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public bool $is_active = true;

    public function render()
    {
        $user = Auth::user();

        $outlets = $user->role === 'owner'
            ? Outlet::where('owner_id', $user->owner_id)->latest()->get()
            : Outlet::where('id', $user->outlet_id)->get();

        return view('livewire.app.outlets.index', [
            'outlets' => $outlets,
            'canManage' => $user->role === 'owner',
        ]);
    }

    public function openCreateModal(): void
    {
        $this->authorizeOwnerOnly();

        [$allowed, $message] = $this->canAddOutlet();

        if (! $allowed) {
            $this->dispatch('notify', message: $message, type: 'error');
            return;
        }

        $this->reset(['editingId', 'name', 'address', 'phone']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(string $outletId): void
    {
        $this->authorizeOwnerOnly();

        $outlet = Outlet::where('owner_id', Auth::user()->owner_id)->findOrFail($outletId);

        $this->editingId = $outlet->id;
        $this->name = $outlet->name;
        $this->address = $outlet->address ?? '';
        $this->phone = $outlet->phone ?? '';
        $this->is_active = $outlet->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeOwnerOnly();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            $outlet = Outlet::where('owner_id', Auth::user()->owner_id)->findOrFail($this->editingId);
            $outlet->update($validated);
            $this->dispatch('notify', message: 'Outlet berhasil diperbarui.');
            $this->showModal = false;
            return;
        }

        // Re-cek limit di sini juga (bukan cuma di openCreateModal), untuk
        // jaga-jaga kalau modal sempat terbuka lama dan kondisi berubah,
        // atau ada percobaan bypass langsung panggil method save().
        [$allowed, $message] = $this->canAddOutlet();

        if (! $allowed) {
            $this->dispatch('notify', message: $message, type: 'error');
            $this->showModal = false;
            return;
        }

        Outlet::create([
            ...$validated,
            'owner_id' => Auth::user()->owner_id,
        ]);

        $this->syncSubscriptionOutletCount();

        $this->dispatch('notify', message: 'Outlet berhasil ditambahkan.');
        $this->showModal = false;
    }

    public function delete(string $outletId): void
    {
        $this->authorizeOwnerOnly();

        Outlet::where('owner_id', Auth::user()->owner_id)->findOrFail($outletId)->delete();

        $this->syncSubscriptionOutletCount();

        $this->dispatch('notify', message: 'Outlet berhasil dihapus.');
    }

    /**
     * Cek apakah owner masih boleh menambah 1 outlet lagi, berdasarkan
     * subscription aktif/trial miliknya dan pricing tier dari plan tersebut.
     *
     * @return array{0: bool, 1: ?string}
     */
    protected function canAddOutlet(): array
    {
        $owner = Auth::user()->owner;

        $subscription = Subscription::where('owner_id', $owner->id)
            ->whereIn('status', ['trial', 'active'])
            ->latest('start_date')
            ->first();

        if (! $subscription) {
            return [false, 'Anda belum memiliki paket subscription aktif. Hubungi admin untuk berlangganan sebelum membuat outlet.'];
        }

        $currentCount = Outlet::where('owner_id', $owner->id)->count();
        $targetCount = $currentCount + 1;

        $tierExists = SubscriptionPricingTier::where('plan_id', $subscription->plan_id)
            ->where('is_active', true)
            ->where('min_outlet', '<=', $targetCount)
            ->where(function ($query) use ($targetCount) {
                $query->whereNull('max_outlet')
                    ->orWhere('max_outlet', '>=', $targetCount);
            })
            ->exists();

        if (! $tierExists) {
            return [false, "Anda sudah mencapai batas maksimal outlet ({$currentCount}) untuk paket saat ini. Silakan upgrade paket Anda untuk menambah outlet."];
        }

        return [true, null];
    }

    protected function syncSubscriptionOutletCount(): void
    {
        $owner = Auth::user()->owner;

        $subscription = Subscription::where('owner_id', $owner->id)
            ->whereIn('status', ['trial', 'active'])
            ->latest('start_date')
            ->first();

        if ($subscription) {
            $subscription->update([
                'current_outlet_count' => Outlet::where('owner_id', $owner->id)->count(),
            ]);
        }
    }

    protected function authorizeOwnerOnly(): void
    {
        abort_unless(Auth::user()->role === 'owner', 403);
    }
}