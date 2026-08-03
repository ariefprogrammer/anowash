<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Layanan</h1>
        <button wire:click="openCreateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Tambah Layanan
        </button>
    </div>

    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Estimasi</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($services as $service)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $service->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $service->category ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $service->estimated_duration_min ? $service->estimated_duration_min.' menit' : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            <button wire:click="openEditModal({{ $service->id }})" class="text-teal-600 hover:underline">Edit</button>
                            <button
                                wire:click="delete({{ $service->id }})"
                                wire:confirm="Yakin ingin menghapus layanan ini?"
                                class="text-red-600 hover:underline"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada layanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        @forelse($services as $service)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold">{{ $service->name }}</h3>
                    <span class="px-2 py-1 rounded text-xs {{ $service->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="text-xs text-gray-400 space-y-1 mb-3">
                    <p><i class="fa-solid fa-tag w-4"></i> {{ $service->category ?? '-' }}</p>
                    <p><i class="fa-solid fa-clock w-4"></i> {{ $service->estimated_duration_min ? $service->estimated_duration_min.' menit' : '-' }}</p>
                </div>
                <div class="flex gap-2 pt-2 border-t">
                    <button
                        wire:click="openEditModal({{ $service->id }})"
                        class="flex items-center gap-1 px-3 py-1.5 rounded bg-teal-50 text-teal-600 text-sm hover:bg-teal-100"
                    >
                        <i class="fa-solid fa-pen text-xs"></i> Edit
                    </button>
                    <button
                        wire:click="delete({{ $service->id }})"
                        wire:confirm="Yakin ingin menghapus layanan ini?"
                        class="flex items-center gap-1 px-3 py-1.5 rounded bg-red-50 text-red-600 text-sm hover:bg-red-100"
                    >
                        <i class="fa-solid fa-trash text-xs"></i> Hapus
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Belum ada layanan.
            </div>
        @endforelse
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Layanan' : 'Tambah Layanan' }}</h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Layanan</label>
                        <input type="text" wire:model="name" class="w-full border rounded px-3 py-2" placeholder="misal: Cuci Mobil, Salon Interior">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <input type="text" wire:model="category" class="w-full border rounded px-3 py-2" placeholder="opsional">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Estimasi Durasi (menit)</label>
                        <input type="number" wire:model="estimated_duration_min" class="w-full border rounded px-3 py-2">
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_active">
                        Aktif
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded border">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>