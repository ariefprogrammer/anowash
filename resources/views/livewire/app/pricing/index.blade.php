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
            <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
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
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" wire:model.live="activeServices.{{ $service->id }}" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 border border-gray-300 rounded-full peer-checked:bg-teal-600 peer-checked:border-teal-600 transition-colors"></div>
                                            <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full shadow-md transition-transform peer-checked:translate-x-5"></div>
                                        </div>
                                        <span class="text-xs {{ ($activeServices[$service->id] ?? false) ? 'text-teal-600' : 'text-gray-400' }}">
                                            {{ ($activeServices[$service->id] ?? false) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </label>
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

            <div class="md:hidden space-y-4">
                @foreach($services as $service)
                    <div class="bg-white rounded-lg shadow p-4">
                        <label class="flex items-center gap-2 mb-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" wire:model.live="activeServices.{{ $service->id }}" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-teal-600 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 bg-white w-5 h-5 rounded-full transition-transform peer-checked:translate-x-5"></div>
                            </div>
                            <span class="font-bold">{{ $service->name }}</span>
                            <span class="text-xs {{ ($activeServices[$service->id] ?? false) ? 'text-teal-600' : 'text-gray-400' }}">
                                ({{ ($activeServices[$service->id] ?? false) ? 'Aktif' : 'Nonaktif' }})
                            </span>
                        </label>

                        <div class="space-y-2">
                            @foreach($categories as $category)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-400">{{ $category->name }}</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model="prices.{{ $service->id }}_{{ $category->id }}"
                                        class="w-28 border rounded px-2 py-1 text-sm"
                                        placeholder="0"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="mt-4 bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">
                Simpan Perubahan
            </button>
        </form>
    @endif
</div>