<div class="p-6">
    <h1 class="text-2xl font-semibold mb-1">Dashboard Marketing</h1>
    <p class="text-gray-600 mb-6">
        {{ $marketing->name }} • Kode Referral: <span class="font-mono font-semibold">{{ $marketing->referral_code }}</span>
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Customer Didapat</p>
            <p class="text-2xl font-semibold mt-1">{{ $totalCustomers }} Owner</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Komisi Sudah Dibayar</p>
            <p class="text-2xl font-semibold mt-1 text-green-700">Rp{{ number_format($commissionPaid, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Komisi Belum Dibayar</p>
            <p class="text-2xl font-semibold mt-1 text-yellow-700">Rp{{ number_format($commissionUnpaid, 0, ',', '.') }}</p>
        </div>
    </div>

    <h2 class="text-lg font-semibold mb-3">Daftar Customer</h2>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3">Nama Usaha</th>
                    <th class="px-4 py-3">Jumlah Outlet</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Bergabung</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($owners as $owner)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $owner->business_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $owner->outlets_count }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $owner->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($owner->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $owner->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada customer.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>