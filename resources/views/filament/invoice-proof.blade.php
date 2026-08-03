<div>
    @if($path)
        <img src="{{ Storage::url($path) }}" alt="Bukti Pembayaran" class="rounded-lg w-full">
    @else
        <p class="text-gray-500">Belum ada bukti bayar diunggah.</p>
    @endif
</div>