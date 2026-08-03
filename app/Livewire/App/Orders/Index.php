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

    public string $payMethod = 'Cash';
    public ?string $amountPaid = null;
    public bool $showPayOrderModal = false;
    public ?int $payOrderId = null;
    public float $payOrderTotal = 0;

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

    public function openPayOrderModal(int $orderId): void
    {
        $order = $this->scopedOrderQuery()->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            $this->dispatch('notify', message: 'Order ini sudah lunas.', type: 'error');
            return;
        }

        $this->payOrderId = $order->id;
        $this->payOrderTotal = $order->total;
        $this->payMethod = 'Cash';
        $this->amountPaid = null;
        $this->showPayOrderModal = true;
    }

    public function closePayOrderModal(): void
    {
        $this->reset(['showPayOrderModal', 'payOrderId', 'payOrderTotal', 'payMethod', 'amountPaid']);
    }

    public function getChangeProperty(): float
    {
        return (float) ($this->amountPaid ?: 0) - $this->payOrderTotal;
    }

    public function confirmPayOrder(): void
    {
        if ($this->payMethod === 'Cash' && (float) ($this->amountPaid ?: 0) < $this->payOrderTotal) {
            $this->addError('amountPaid', 'Nominal dibayar kurang dari total tagihan.');
            return;
        }

        $order = $this->scopedOrderQuery()->findOrFail($this->payOrderId);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $this->payMethod,
            'paid_at' => now(),
        ]);

        $this->dispatch('notify', message: 'Pembayaran order ditandai lunas.');
        $this->closePayOrderModal();
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