<div class="p-6">
    <h1 class="text-2xl font-semibold mb-6">Harga Layanan per Outlet</h1>

    <div class="mb-6 max-w-xs">
        <label class="block text-sm font-medium mb-1">Pilih Outlet</label>
        <select wire:model.live="outletId" class="w-full border rounded px-3 py-2">
            @foreach($outlets as $outlet)
                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
            @endforeach
        </select>
    </div>

    @if(!$outletId)
        <p class="text-gray-500">Buat outlet terlebih dahulu.</p>
    @elseif($services->isEmpty())
        <p class="text-gray-500">Buat layanan terlebih dahulu di menu Layanan.</p>
    @elseif($categories->isEmpty())
        <p class="text-gray-500">Belum ada kategori kendaraan tersedia.</p>
    @else
        <form wire:submit="save">
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3">Aktif</th>
                            <th class="px-4 py-3">Layanan</th>
                            @foreach($categories as $category)
                                <th class="px-4 py-3">{{ $category->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($services as $service)
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model="activeServices.{{ $service->id }}">
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $service->name }}</td>
                                @foreach($categories as $category)
                                    <td class="px-4 py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="prices.{{ $service->id }}_{{ $category->id }}"
                                            class="w-28 border rounded px-2 py-1"
                                            placeholder="0"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="mt-4 bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
                Simpan Perubahan
            </button>
        </form>
    @endif
</div>