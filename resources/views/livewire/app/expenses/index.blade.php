<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Pengeluaran</h1>
        <button wire:click="openCreateModal" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
            + Catat Pengeluaran
        </button>
    </div>

    <div class="flex gap-3 mb-4">
        @if($outlets->count() > 1)
            <select wire:model.live="filterOutletId" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Outlet</option>
                @foreach($outlets as $outlet)
                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        @endif

        <input type="month" wire:model.live="filterMonth" class="border rounded px-3 py-2 text-sm">
    </div>

    <div class="bg-teal-50 border border-teal-200 rounded p-3 mb-4 text-sm">
        Total pengeluaran periode ini: <span class="font-semibold">Rp{{ number_format($totalFiltered, 0, ',', '.') }}</span>
    </div>

    <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Outlet</th>
                    <th class="px-4 py-3">Nominal</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($expenses as $expense)
                    <tr>
                        <td class="px-4 py-3 text-gray-600">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $expense->title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $expense->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $expense->outlet->name }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <button wire:click="openEditModal({{ $expense->id }})" class="text-teal-600 hover:underline">Edit</button>
                            <button
                                wire:click="delete({{ $expense->id }})"
                                wire:confirm="Yakin ingin menghapus catatan pengeluaran ini?"
                                class="text-red-600 hover:underline"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada pengeluaran tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        @forelse($expenses as $expense)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold">{{ $expense->title }}</h3>
                    <span class="font-bold text-teal-600">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs text-gray-400 space-y-1 mb-3">
                    <p><i class="fa-solid fa-calendar w-4"></i> {{ $expense->expense_date->format('d M Y') }}</p>
                    <p><i class="fa-solid fa-tag w-4"></i> {{ $expense->category->name ?? '-' }}</p>
                    <p><i class="fa-solid fa-store w-4"></i> {{ $expense->outlet->name }}</p>
                </div>
                <div class="flex gap-2 pt-2 border-t">
                    <button
                        wire:click="openEditModal({{ $expense->id }})"
                        class="flex items-center gap-1 px-3 py-1.5 rounded bg-teal-50 text-teal-600 text-sm hover:bg-teal-100"
                    >
                        <i class="fa-solid fa-pen text-xs"></i> Edit
                    </button>
                    <button
                        wire:click="delete({{ $expense->id }})"
                        wire:confirm="Yakin ingin menghapus catatan pengeluaran ini?"
                        class="flex items-center gap-1 px-3 py-1.5 rounded bg-red-50 text-red-600 text-sm hover:bg-red-100"
                    >
                        <i class="fa-solid fa-trash text-xs"></i> Hapus
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Belum ada pengeluaran tercatat.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit Pengeluaran' : 'Catat Pengeluaran' }}</h2>

                <form wire:submit="save" class="space-y-4">
                    @if($outlets->count() > 1)
                        <div>
                            <label class="block text-sm font-medium mb-1">Outlet</label>
                            <select wire:model="outlet_id" class="w-full border rounded px-3 py-2">
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1">Judul</label>
                        <input type="text" wire:model="title" class="w-full border rounded px-3 py-2" placeholder="misal: Tagihan Internet Juli">
                        @error('title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <select wire:model="expense_category_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nominal (Rp)</label>
                        <input type="number" step="0.01" wire:model="amount" class="w-full border rounded px-3 py-2">
                        @error('amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Tanggal</label>
                        <input type="date" wire:model="expense_date" class="w-full border rounded px-3 py-2">
                        @error('expense_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        <span class="text-xs text-gray-400">Default hari ini, bisa diubah jika perlu.</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
                        <textarea wire:model="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
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