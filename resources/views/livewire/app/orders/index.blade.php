<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Order</h1>
        <a href="{{ route('app.orders.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Order Baru
        </a>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <select wire:model.live="statusFilter" class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <input type="date" wire:model.live="dateFilter" class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm">

        @if($dateFilter !== now()->toDateString())
            <button wire:click="$set('dateFilter', '{{ now()->toDateString() }}')" class="text-xs text-teal-600 hover:underline">
                Kembali ke Hari Ini
            </button>
        @endif
    </div>

    <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Plat Nomor</th>
                    <th class="px-4 py-3">Outlet</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Pembayaran</th>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($orders as $order)
                    @php
                        $badgeColor = match($order->status) {
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-purple-100 text-purple-700',
                            'paid' => 'bg-teal-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        };

                        $badgePaymentStatus = match($order->payment_status){
                            'unpaid' => 'bg-red-100 text-red-700',
                            'paid' => 'bg-teal-100 text-green-700',
                        };
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $order->plate_number_snapshot }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->outlet->name }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $badgeColor }}">{{ ucfirst(str_replace('_',' ', $order->status)) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $badgePaymentStatus }}">{{ ucfirst(str_replace('_',' ', $order->payment_status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 space-x-2">
                            @if($order->status === 'pending')
                                <button wire:click="updateStatus({{ $order->id }}, 'in_progress')" class="bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-red-700"><i class="fa-solid fa-play text-xs"></i> Mulai Kerjakan</button>
                                <button wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Batalkan order ini?" class="text-red-600 hover:underline">Batalkan</button>
                            @elseif($order->status === 'in_progress')
                                <a href="{{ route('app.orders.workers', $order) }}" class="bg-amber-600 text-white px-3 py-1.5 rounded hover:bg-red-700"><i class="fa-solid fa-user-gear"></i> Kelola Karyawan</a>
                                <button wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Batalkan order ini?" class="text-red-600 hover:underline">Batalkan</button>
                            @elseif($order->status === 'completed')
                                <a href="{{ route('app.orders.workers', $order) }}" class="text-gray-500 hover:underline"><i class="fa-solid fa-money-bill-wave"></i> Lihat Komisi</a>
                            @endif

                            @if($order->payment_status !== 'paid')
                                <button
                                    wire:click="openPayOrderModal({{ $order->id }})"
                                    class="text-green-600 hover:underline block"
                                >
                                    Tandai Lunas
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        @forelse($orders as $order)
            @php
                $badgeColor = match($order->status) {
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'in_progress' => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-purple-100 text-purple-700',
                    'paid' => 'bg-teal-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                };

                $badgePaymentStatus = match($order->payment_status){
                    'unpaid' => 'bg-red-100 text-red-700',
                    'paid' => 'bg-teal-100 text-green-700',
                };
            @endphp
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold">{{ $order->plate_number_snapshot }}</h3>
                    <span class="font-bold text-teal-600">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                </div>

                <div class="text-xs text-gray-400 space-y-1 mb-3">
                    <p><i class="fa-solid fa-store w-4"></i> {{ $order->outlet->name }}</p>
                    <p><i class="fa-solid fa-clock w-4"></i> {{ $order->created_at->format('d M Y H:i') }}</p>
                </div>

                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded text-xs {{ $badgeColor }}">{{ ucfirst(str_replace('_',' ', $order->status)) }}</span>
                    <span class="px-2 py-1 rounded text-xs {{ $badgePaymentStatus }}">{{ ucfirst(str_replace('_',' ', $order->payment_status)) }}</span>
                </div>

                <div class="flex flex-wrap gap-2 pt-2 border-t">
                    @if($order->status === 'pending')
                        <button wire:click="updateStatus({{ $order->id }}, 'in_progress')" class="flex items-center gap-1 px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                            <i class="fa-solid fa-play text-xs"></i> Mulai Kerjakan
                        </button>
                        <button wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Batalkan order ini?" class="flex items-center gap-1 px-3 py-1.5 rounded bg-red-50 text-red-600 text-sm hover:bg-red-100">
                            <i class="fa-solid fa-xmark text-xs"></i> Batalkan
                        </button>
                    @elseif($order->status === 'in_progress')
                        <a href="{{ route('app.orders.workers', $order) }}" class="flex items-center gap-1 px-3 py-1.5 rounded bg-amber-600 text-white text-sm hover:bg-amber-700">
                            <i class="fa-solid fa-user-gear text-xs"></i> Kelola Karyawan
                        </a>
                        <button wire:click="updateStatus({{ $order->id }}, 'cancelled')" wire:confirm="Batalkan order ini?" class="flex items-center gap-1 px-3 py-1.5 rounded bg-red-50 text-red-600 text-sm hover:bg-red-100">
                            <i class="fa-solid fa-xmark text-xs"></i> Batalkan
                        </button>
                    @elseif($order->status === 'completed')
                        <a href="{{ route('app.orders.workers', $order) }}" class="flex items-center gap-1 px-3 py-1.5 rounded bg-gray-50 text-gray-600 text-sm hover:bg-gray-100">
                            <i class="fa-solid fa-money-bill-wave text-xs"></i> Lihat Komisi
                        </a>
                    @endif

                    @if($order->payment_status !== 'paid')
                        <button
                            wire:click="openPayOrderModal({{ $order->id }})"
                            class="flex items-center gap-1 px-3 py-1.5 rounded bg-teal-50 text-teal-600 text-sm hover:bg-teal-100"
                        >
                            <i class="fa-solid fa-circle-check text-xs"></i> Tandai Lunas
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Belum ada order.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>

    @if($showPayOrderModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="closePayOrderModal">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">Konfirmasi Pembayaran</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                        <select wire:model.live="payMethod" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2">
                            <option value="Cash">Cash</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>

                    @if($payMethod === 'Cash')
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal Dibayar (Rp)</label>
                            <input type="number" step="0.01" wire:model.live="amountPaid" class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2">
                            @error('amountPaid') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="bg-gray-50 rounded p-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>Total Tagihan</span>
                            <span>Rp{{ number_format($payOrderTotal, 0, ',', '.') }}</span>
                        </div>
                        @if($payMethod === 'Cash')
                            <div class="flex justify-between">
                                <span>Dibayar</span>
                                <span>Rp{{ number_format((float)($amountPaid ?: 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-semibold border-t pt-1">
                                <span>Kembalian</span>
                                <span class="{{ $this->change < 0 ? 'text-red-600' : '' }}">
                                    Rp{{ number_format($this->change, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" wire:click="closePayOrderModal" class="px-4 py-2 rounded border">Batal</button>
                    <button
                        type="button"
                        wire:click="confirmPayOrder"
                        @disabled($payMethod === 'Cash' && (float)($amountPaid ?: 0) < $payOrderTotal)
                        class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Konfirmasi & Tandai Lunas
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>