<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Aturan Komisi</h1>
        <button wire:click="openCreateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Tambah Aturan
        </button>
    </div>

    <p class="text-sm text-gray-500 mb-4">
        Aturan spesifik per-layanan akan dipakai lebih dulu. Kalau suatu layanan tidak punya aturan khusus, sistem akan pakai aturan <strong>Default (Semua Layanan)</strong>.
    </p>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Layanan</th>
                    <th class="px-4 py-3">Basis</th>
                    <th class="px-4 py-3">Nilai</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($rules as $rule)
                    <tr>
                        <td class="px-4 py-3 font-medium">
                            @if($rule->service_id)
                                {{ $rule->service->name ?? '(layanan dihapus)' }}
                            @else
                                <span class="text-purple-600">Default (Semua Layanan)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $rule->basis === 'percentage' ? 'Persentase' : 'Nominal Tetap' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $rule->basis === 'percentage' ? $rule->value.'%' : 'Rp'.number_format($rule->value, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <button wire:click="openEditModal({{ $rule->id }})" class="text-teal-600 hover:underline">Edit</button>
                            <button
                                wire:click="delete({{ $rule->id }})"
                                wire:confirm="Yakin ingin menghapus aturan ini?"
                                class="text-red-600 hover:underline"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada aturan komisi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Aturan' : 'Tambah Aturan' }}</h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Layanan</label>
                        <select wire:model="service_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- Default (Semua Layanan) --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Basis Perhitungan</label>
                        <select wire:model="basis" class="w-full border rounded px-3 py-2">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nilai</label>
                        <input type="number" step="0.01" wire:model="value" class="w-full border rounded px-3 py-2">
                        @error('value') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded border">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>