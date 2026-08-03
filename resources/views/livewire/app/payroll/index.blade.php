<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Payroll</h1>
        <button wire:click="openGenerateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Generate Payroll
        </button>
    </div>

    <div class="mb-4">
        <select wire:model.live="statusFilter" class="border rounded px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="unpaid">Siap Dibayar</option>
            <option value="paid">Sudah Dibayar</option>
        </select>
    </div>

    <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Outlet</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Rincian</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($records as $record)
                    @php
                        $badgeColor = match($record->status) {
                            'draft' => 'bg-gray-100 text-gray-600',
                            'unpaid' => 'bg-yellow-100 text-yellow-700',
                            'paid' => 'bg-green-100 text-green-700',
                        };
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium align-top">{{ $record->employee->name }}</td>
                        <td class="px-4 py-3 text-gray-600 align-top">{{ $record->employee->outlet->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 align-top">
                            {{ $record->period_start->format('d M') }} - {{ $record->period_end->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            <ul class="text-xs text-gray-600 space-y-0.5">
                                @if($record->type === 'flat')
                                    <li>Gaji Tetap: Rp{{ number_format($record->base_amount, 0, ',', '.') }}</li>
                                @else
                                    <li>Komisi: Rp{{ number_format($record->commission_total, 0, ',', '.') }}</li>
                                @endif
                                @foreach($record->details as $detail)
                                    <li>{{ $detail->name_snapshot }}: Rp{{ number_format($detail->amount, 0, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-3 font-medium align-top">Rp{{ number_format($record->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 align-top">
                            <span class="px-2 py-1 rounded text-xs {{ $badgeColor }}">
                                {{ ['draft' => 'Draft', 'unpaid' => 'Siap Dibayar', 'paid' => 'Sudah Dibayar'][$record->status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 space-x-2 align-top">
                            @if($record->status !== 'paid')
                                <button wire:click="openEditPayrollModal({{ $record->id }})" class="text-teal-600 hover:underline text-xs block">Edit</button>
                            @endif
                            @if($record->status === 'draft')
                                <button wire:click="markUnpaid({{ $record->id }})" class="text-yellow-600 hover:underline text-xs block">Siapkan Bayar</button>
                            @elseif($record->status === 'unpaid')
                                <button
                                    wire:click="markPaid({{ $record->id }})"
                                    wire:confirm="Tandai payroll ini sudah dibayar?"
                                    class="text-green-600 hover:underline text-xs block"
                                >
                                    Tandai Dibayar
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">{{ $record->paid_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada data payroll.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        @forelse($records as $record)
            @php
                $badgeColor = match($record->status) {
                    'draft' => 'bg-gray-100 text-gray-600',
                    'unpaid' => 'bg-yellow-100 text-yellow-700',
                    'paid' => 'bg-green-100 text-green-700',
                };
            @endphp
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold">{{ $record->employee->name }}</h3>
                    <span class="px-2 py-1 rounded text-xs {{ $badgeColor }}">
                        {{ ['draft' => 'Draft', 'unpaid' => 'Siap Dibayar', 'paid' => 'Sudah Dibayar'][$record->status] }}
                    </span>
                </div>

                <div class="text-xs text-gray-400 space-y-1 mb-3">
                    <p><i class="fa-solid fa-store w-4"></i> {{ $record->employee->outlet->name ?? '-' }}</p>
                    <p><i class="fa-solid fa-calendar w-4"></i> {{ $record->period_start->format('d M') }} - {{ $record->period_end->format('d M Y') }}</p>
                </div>

                <div class="bg-gray-50 rounded p-3 mb-3">
                    <ul class="text-xs text-gray-600 space-y-0.5">
                        @if($record->type === 'flat')
                            <li>Gaji Tetap: Rp{{ number_format($record->base_amount, 0, ',', '.') }}</li>
                        @else
                            <li>Komisi: Rp{{ number_format($record->commission_total, 0, ',', '.') }}</li>
                        @endif
                        @foreach($record->details as $detail)
                            <li>{{ $detail->name_snapshot }}: Rp{{ number_format($detail->amount, 0, ',', '.') }}</li>
                        @endforeach
                    </ul>
                    <div class="flex justify-between items-center pt-2 mt-2 border-t font-bold text-sm">
                        <span>Total</span>
                        <span>Rp{{ number_format($record->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-2 border-t">
                    @if($record->status !== 'paid')
                        <button
                            wire:click="openEditPayrollModal({{ $record->id }})"
                            class="flex items-center gap-1 px-3 py-1.5 rounded bg-teal-50 text-teal-600 text-sm hover:bg-teal-100"
                        >
                            <i class="fa-solid fa-pen text-xs"></i> Edit
                        </button>
                    @endif
                    @if($record->status === 'draft')
                        <button
                            wire:click="markUnpaid({{ $record->id }})"
                            class="flex items-center gap-1 px-3 py-1.5 rounded bg-yellow-50 text-yellow-600 text-sm hover:bg-yellow-100"
                        >
                            <i class="fa-solid fa-clock text-xs"></i> Siapkan Bayar
                        </button>
                    @elseif($record->status === 'unpaid')
                        <button
                            wire:click="markPaid({{ $record->id }})"
                            wire:confirm="Tandai payroll ini sudah dibayar?"
                            class="flex items-center gap-1 px-3 py-1.5 rounded bg-green-50 text-green-600 text-sm hover:bg-green-100"
                        >
                            <i class="fa-solid fa-check text-xs"></i> Tandai Dibayar
                        </button>
                    @else
                        <span class="text-gray-400 text-xs py-1.5">Dibayar {{ $record->paid_at?->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Belum ada data payroll.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $records->links() }}
    </div>

    @if($showGenerateModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showGenerateModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">Generate Payroll</h2>

                <form wire:submit="generate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Outlet</label>
                        <select wire:model="outletId" class="w-full border rounded px-3 py-2">
                            <option value="">-- Pilih Outlet --</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                        @error('outletId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Awal Periode</label>
                            <input type="date" wire:model="periodStart" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Akhir Periode</label>
                            <input type="date" wire:model="periodEnd" class="w-full border rounded px-3 py-2">
                            @error('periodEnd') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-500">
                        Sistem akan membuat slip payroll untuk semua karyawan aktif di outlet ini, termasuk tunjangan yang sudah di-assign ke masing-masing karyawan.
                    </p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showGenerateModal', false)" class="px-4 py-2 rounded border">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showEditModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showEditModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4">Sesuaikan Payroll</h2>

                <form wire:submit="savePayroll" class="space-y-4">
                    @if($editType === 'flat')
                        <div>
                            <label class="block text-sm font-medium mb-1">Gaji Tetap (Rp)</label>
                            <input type="number" step="0.01" wire:model.live="editBaseAmount" class="w-full border rounded px-3 py-2">
                            @error('editBaseAmount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium mb-1">Total Komisi (Rp)</label>
                            <input type="number" step="0.01" wire:model.live="editCommissionTotal" class="w-full border rounded px-3 py-2">
                            @error('editCommissionTotal') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium">Rincian Tunjangan</label>
                            <button type="button" wire:click="addEditDetailRow" class="text-teal-600 text-xs hover:underline">+ Tambah Baris</button>
                        </div>

                        <div class="space-y-2">
                            @foreach($editDetails as $index => $detail)
                                <div class="flex gap-2">
                                    <input type="text" wire:model.live="editDetails.{{ $index }}.name" class="flex-1 border rounded px-3 py-2 text-sm" placeholder="Nama tunjangan">
                                    <input type="number" step="0.01" wire:model.live="editDetails.{{ $index }}.amount" class="w-32 border rounded px-3 py-2 text-sm" placeholder="Nominal">
                                    <button type="button" wire:click="removeEditDetailRow({{ $index }})" class="text-red-600 px-2">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        @error('editDetails.*.name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        @error('editDetails.*.amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-gray-50 rounded p-3 flex justify-between font-semibold text-sm">
                        <span>Total Payroll</span>
                        <span>Rp{{ number_format($this->editTotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 rounded border">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>