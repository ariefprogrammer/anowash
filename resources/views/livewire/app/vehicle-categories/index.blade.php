<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Kategori Kendaraan</h1>
        <button wire:click="openCreateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Tambah Kategori
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($categories as $category)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $category->description ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $category->owner_id ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $category->owner_id ? 'Milik Anda' : 'Bawaan Sistem' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 space-x-2">
                            @if($category->owner_id)
                                <button wire:click="openEditModal({{ $category->id }})" class="text-teal-600 hover:underline">Edit</button>
                                <button
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="Yakin ingin menghapus kategori ini?"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">Tidak dapat diubah</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                        <input type="text" wire:model="name" class="w-full border rounded px-3 py-2" placeholder="misal: SUV, MPV, Pickup">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
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