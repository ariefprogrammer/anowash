<div class="p-6">
    <h1 class="text-2xl font-semibold mb-6">Order Baru</h1>

    <form wire:submit="save" class="space-y-6">
        @if($outlets->count() > 0)
            <div>
                <label class="block text-sm font-medium mb-1">Outlet</label>
                <select wire:model.live="outletId" class="w-full border rounded px-3 py-2">
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Plat Nomor</label>
                <input type="text" wire:model.live.debounce.500ms="plateNumber" class="w-full border rounded px-3 py-2 uppercase" placeholder="B 1234 XYZ">
                @error('plateNumber') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                @if($vehicleFound)
                    <span class="text-xs text-green-600">Kendaraan ditemukan — data terisi otomatis.</span>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Kategori Kendaraan</label>
                <select wire:model.live="vehicleCategoryId" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('vehicleCategoryId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div x-data="{ show: $wire.entangle('showCustomerDetails') }">
            <button
                type="button"
                @click="show = !show"
                class="inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-full transition-colors duration-150"
                :class="show ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-teal-50 text-teal-600 hover:bg-teal-100'"
            >
                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5Z" />
                </svg>
                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16ZM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                </svg>
                <span x-text="show ? 'Sembunyikan' : 'Isi Detail Pelanggan (opsional)'"></span>
            </button>

            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="grid grid-cols-2 gap-4 mt-3"
            >
                <div>
                    <label class="block text-sm font-medium mb-1">Merek (opsional)</label>
                    <input type="text" wire:model="vehicleBrand" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Model (opsional)</label>
                    <input type="text" wire:model="vehicleModel" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Pelanggan (opsional)</label>
                    <input type="text" wire:model="customerName" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">No. HP Pelanggan (opsional)</label>
                    <input type="text" wire:model="customerPhone" class="w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Catatan</label>
                    <textarea wire:model="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium">Item Layanan</label>
                <button type="button" wire:click="addItem" class="text-teal-600 text-sm hover:underline">+ Tambah Item</button>
            </div>

            <div class="space-y-2">
                @foreach($items as $index => $item)
                    <div class="flex gap-2 items-start">
                        <select wire:model="items.{{ $index }}.service_id" wire:change="onServiceChange({{ $index }})" class="flex-1 border rounded px-3 py-2">
                            <option value="">-- Pilih Layanan --</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>

                        <input type="number" wire:model="items.{{ $index }}.quantity" min="1" class="w-20 border rounded px-3 py-2" placeholder="Qty">

                        <input type="number" step="0.01" wire:model="items.{{ $index }}.price" class="w-32 border rounded px-3 py-2" placeholder="Harga">

                        @if(count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-600 px-2">&times;</button>
                        @endif
                    </div>
                @endforeach
            </div>
            @error('items') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="bg-gray-50 rounded p-4 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>Rp{{ number_format($this->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Diskon</span><span>Rp{{ number_format((float)($discount ?: 0), 0, ',', '.') }}</span></div>
            <div class="flex justify-between font-semibold text-base border-t pt-1"><span>Total</span><span>Rp{{ number_format($this->total, 0, ',', '.') }}</span></div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('app.orders') }}" class="px-4 py-2 rounded border">Batal</a>
            <button type="button" wire:click="openPayModal" class="px-4 py-2 rounded bg-amber-600 text-white hover:bg-amber-800">
                Simpan dan Bayar
            </button>
            <button type="submit" class="px-4 py-2 rounded bg-teal-600 text-white hover:bg-teal-700">
                Simpan Order
            </button>
        </div>

        @if($showPayModal)
            <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="closePayModal">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Konfirmasi Pembayaran</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Diskon (Rp)</label>
                            <input type="number" step="0.01" wire:model.live="discount" class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full border rounded px-3 py-2">
                                <option value="Cash">Cash</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Transfer">Transfer</option>
                            </select>
                        </div>

                        <div class="bg-gray-50 rounded p-3 space-y-1 text-sm">
                            <div class="flex justify-between"><span>Subtotal</span><span>Rp{{ number_format($this->subtotal, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span>Diskon</span><span>Rp{{ number_format((float)($discount ?: 0), 0, ',', '.') }}</span></div>
                            <div class="flex justify-between font-semibold border-t pt-1"><span>Total Dibayar</span><span>Rp{{ number_format($this->total, 0, ',', '.') }}</span></div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" wire:click="closePayModal" class="px-4 py-2 rounded border">Batal</button>
                        <button type="button" wire:click="confirmPay" class="px-4 py-2 rounded bg-amber-600 text-white hover:bg-amber-700">
                            Konfirmasi & Simpan
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>