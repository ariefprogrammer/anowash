<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Kelola Karyawan — Order #{{ $order->id }}</h1>
        <span class="text-sm text-gray-500">{{ $order->plate_number_snapshot }} • {{ $order->outlet->name }}</span>
    </div>

    @if(in_array($order->status, ['completed', 'paid']))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm rounded p-3 mb-6">
            Order ini sudah selesai — assignment karyawan & komisi sudah dikunci dan tidak bisa diubah.
        </div>
    @endif

    <div class="space-y-6">
        @foreach($order->items as $item)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-medium">{{ $item->service_name_snapshot }}</h3>
                    <span class="text-sm text-gray-500">
                        Line total: Rp{{ number_format($item->line_total, 0, ',', '.') }} •
                        Dasar komisi: Rp{{ number_format($preview[$item->id]['base'], 0, ',', '.') }}
                    </span>
                </div>

                @error("assignments.{$item->id}")
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded p-2 mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <label class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                    <input type="checkbox" wire:model.live="uniformSplitType.{{ $item->id }}" class="rounded">
                    Samakan tipe pembagian untuk semua karyawan di layanan ini
                </label>

                <div class="space-y-2">
                    @foreach($assignments[$item->id] as $index => $row)
                        <div class="flex gap-2 items-center" wire:key="row-{{ $item->id }}-{{ $index }}">
                            <select
                                wire:model.live="assignments.{{ $item->id }}.{{ $index }}.employee_id"
                                class="flex-1 border rounded px-3 py-2 text-sm"
                                @disabled(in_array($order->status, ['completed', 'paid']))
                            >
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>

                            <select
                                wire:model.live="assignments.{{ $item->id }}.{{ $index }}.split_type"
                                class="border rounded px-3 py-2 text-sm"
                                @disabled(in_array($order->status, ['completed', 'paid']))
                            >
                                <option value="equal">Rata</option>
                                <option value="percentage">Persen (%)</option>
                                <option value="fixed_amount">Nominal Tetap</option>
                            </select>

                            @if($row['split_type'] !== 'equal')
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model.live="assignments.{{ $item->id }}.{{ $index }}.split_value"
                                    class="w-28 border rounded px-3 py-2 text-sm"
                                    placeholder="{{ $row['split_type'] === 'percentage' ? '%' : 'Rp' }}"
                                    @disabled(in_array($order->status, ['completed', 'paid']))
                                >
                            @endif

                            <span class="w-28 text-sm text-right text-gray-600">
                                Rp{{ number_format($preview[$item->id]['rows'][$index] ?? 0, 0, ',', '.') }}
                            </span>

                            @if(count($assignments[$item->id]) > 1 && !in_array($order->status, ['completed', 'paid']))
                                <button type="button" wire:click="removeRow({{ $item->id }}, {{ $index }})" class="text-red-600 px-2">&times;</button>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(!in_array($order->status, ['completed', 'paid']))
                    <button type="button" wire:click="addRow({{ $item->id }})" class="text-teal-600 text-sm mt-2 hover:underline">
                        + Tambah Karyawan
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    @if(!in_array($order->status, ['completed', 'paid']))
        <div class="flex justify-end gap-2 mt-6">
            <button wire:click="save(false)" class="px-4 py-2 rounded border">
                Simpan Sementara
            </button>
            <button
                wire:click="save(true)"
                wire:confirm="Setelah diselesaikan, komisi akan dikunci dan tidak bisa diubah. Lanjutkan?"
                class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled($errors->any())
            >
                Selesaikan & Kunci Komisi
            </button>
        </div>
    @endif
</div>