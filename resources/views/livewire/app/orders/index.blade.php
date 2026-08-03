<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Order</h1>
        <a href="{{ route('app.orders.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Order Baru
        </a>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <select wire:model.live="statusFilter" class="border rounded px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <input type="date" wire:model.live="dateFilter" class="border rounded px-3 py-2 text-sm">

        @if($dateFilter !== now()->toDateString())
            <button wire:click="$set('dateFilter', '{{ now()->toDateString() }}')" class="text-xs text-teal-600 hover:underline">
                Kembali ke Hari Ini
            </button>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
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
                            'paid' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        };

                        $badgePaymentStatus = match($order->payment_status){
                            'unpaid' => 'bg-red-100 text-red-700',
                            'paid' => 'bg-green-100 text-green-700',
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
                                    wire:click="updatePaymentStatus({{ $order->id }})"
                                    wire:confirm="Tandai order ini sudah lunas?"
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

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>