<?php

namespace App\Livewire\App\Orders;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $dateFilter = '';

    public function mount(): void
    {
        $this->dateFilter = session('orders.dateFilter', now()->toDateString());
    }

    public function updatedDateFilter(): void
    {
        session(['orders.dateFilter' => $this->dateFilter]);
    }

    protected array $allowedTransitions = [
        'pending' => ['in_progress', 'cancelled'],
        'in_progress' => ['cancelled'],
    ];

    public function updateStatus(int $orderId, string $newStatus): void
    {
        $order = $this->scopedOrderQuery()->findOrFail($orderId);

        if (! in_array($newStatus, $this->allowedTransitions[$order->status] ?? [], true)) {
            $this->dispatch('notify', message: 'Perubahan status tidak valid.', type: 'error');
            return;
        }

        $order->update([
            'status' => $newStatus,
            ...($newStatus === 'paid' ? ['paid_at' => now()] : []),
        ]);

        $this->dispatch('notify', message: 'Status order diperbarui.');
    }

    public function updatePaymentStatus(int $orderId): void
    {
        $order = $this->scopedOrderQuery()->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            $this->dispatch('notify', message: 'Order ini sudah lunas.', type: 'error');
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->dispatch('notify', message: 'Pembayaran order ditandai lunas.');
    }

    protected function scopedOrderQuery()
    {
        $user = Auth::user();
        $query = Order::query();

        if ($user->role === 'admin_outlet') {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            $query->whereIn('outlet_id', Outlet::where('owner_id', $user->owner_id)->pluck('id'));
        }

        return $query;
    }

    public function render()
    {
        $query = $this->scopedOrderQuery()->with('outlet')->latest();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateFilter) {
            $query->whereDate('created_at', $this->dateFilter);
        }

        return view('livewire.app.orders.index', [
            'orders' => $query->paginate(15),
        ]);
    }
}