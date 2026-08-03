<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Outlet</h1>
        @if($canManage)
            <button wire:click="openCreateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
                + Tambah Outlet
            </button>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Alamat</th>
                    <th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">Status</th>
                    @if($canManage)
                        <th class="px-4 py-3">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($outlets as $outlet)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $outlet->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $outlet->address ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $outlet->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $outlet->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $outlet->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        @if($canManage)
                            <td class="px-4 py-3 space-x-2">
                                <button wire:click="openEditModal('{{ $outlet->id }}')" class="text-teal-600 hover:underline">Edit</button>
                                <button
                                    wire:click="delete('{{ $outlet->id }}')"
                                    wire:confirm="Yakin ingin menghapus outlet ini?"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 5 : 4 }}" class="px-4 py-8 text-center text-gray-500">
                            Belum ada outlet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">
                    {{ $editingId ? 'Edit Outlet' : 'Tambah Outlet' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Outlet</label>
                        <input type="text" wire:model="name" class="w-full border rounded px-3 py-2">
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">No. Telepon</label>
                        <input type="text" wire:model="phone" class="w-full border rounded px-3 py-2">
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_active">
                        Outlet Aktif
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded border">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>