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
                        @php $hasRemoveBtn = count($assignments[$item->id]) > 1 && !in_array($order->status, ['completed', 'paid']); @endphp
                        <div
                            class="rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-50 p-3 transition-colors"
                            wire:key="row-{{ $item->id }}-{{ $index }}"
                        >
                            {{-- Baris 1: nama karyawan --}}
                            <div class="flex items-center gap-2 min-w-0 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </div>
                                <select
                                    wire:model.live="assignments.{{ $item->id }}.{{ $index }}.employee_id"
                                    class="w-full min-w-0 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs"
                                    @disabled(in_array($order->status, ['completed', 'paid']))
                                >
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Baris 2: tipe komisi, nominal komisi, tombol hapus --}}
                            <div class="grid items-center gap-2" style="grid-template-columns: {{ $hasRemoveBtn ? '4fr 2fr 1fr' : '4fr 2fr' }}">
                                <div class="min-w-0 {{ $row['split_type'] !== 'equal' ? 'flex gap-1' : '' }}">
                                    <select
                                        wire:model.live="assignments.{{ $item->id }}.{{ $index }}.split_type"
                                        class="w-full min-w-0 {{ $row['split_type'] !== 'equal' ? 'flex-[1.6]' : '' }} bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs"
                                        @disabled(in_array($order->status, ['completed', 'paid']))
                                    >
                                        <option value="equal">Rata</option>
                                        <option value="percentage">Persen (%)</option>
                                        <option value="fixed_amount">Nominal</option>
                                    </select>

                                    @if($row['split_type'] !== 'equal')
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model.live="assignments.{{ $item->id }}.{{ $index }}.split_value"
                                            class="min-w-[64px] flex-1 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs"
                                            placeholder="{{ $row['split_type'] === 'percentage' ? '%' : 'Rp' }}"
                                            @disabled(in_array($order->status, ['completed', 'paid']))
                                        >
                                    @endif
                                </div>

                                <div class="text-right min-w-0">
                                    <span class="block text-sm font-bold text-teal-700 tabular-nums truncate">
                                        Rp{{ number_format($preview[$item->id]['rows'][$index] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>

                                @if($hasRemoveBtn)
                                    <div class="flex justify-center">
                                        <button
                                            type="button"
                                            wire:click="removeRow({{ $item->id }}, {{ $index }})"
                                            class="w-6 h-6 rounded-full flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors text-sm"
                                            aria-label="Hapus karyawan"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                @endif
                            </div>
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