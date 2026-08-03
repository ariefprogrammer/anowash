<div class="p-6">
    <h1 class="text-2xl font-semibold mb-6">Laporan Bulanan</h1>

    <div class="flex gap-3 mb-6">
        <input type="month" wire:model.live="period" class="border rounded px-3 py-2 text-sm">

        @if($outlets->count() > 1)
            <select wire:model.live="outletId" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua Outlet</option>
                @foreach($outlets as $outlet)
                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <p class="text-sm text-gray-500 mb-4">
        Periode: {{ $periodStart->translatedFormat('d F Y') }} - {{ $periodEnd->translatedFormat('d F Y') }}
    </p>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Jumlah Kendaraan</p>
            <p class="text-xl font-semibold mt-1">{{ $vehicleCount }} unit</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Omset</p>
            <p class="text-xl font-semibold mt-1">Rp{{ number_format($revenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Pengeluaran</p>
            <p class="text-xl font-semibold mt-1 text-red-600">Rp{{ number_format($expenseTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-xs text-gray-500">Gaji Karyawan</p>
            <p class="text-xl font-semibold mt-1 text-red-600">Rp{{ number_format($payrollTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 {{ $netProfit >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
            <p class="text-xs text-gray-500">Laba Bersih</p>
            <p class="text-xl font-semibold mt-1 {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                Rp{{ number_format($netProfit, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Detail per karyawan --}}
    <h2 class="text-lg font-semibold mb-3">Detail Kinerja Karyawan</h2>

    <div class="space-y-4">
        @forelse($employeeReports as $report)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold">{{ $report['employee']->name }}</p>
                        <p class="text-xs text-gray-500">{{ $report['employee']->outlet->name ?? '-' }}</p>
                    </div>
                    @if($report['payroll_slips']->count() > 1)
                        <span class="text-xs text-gray-400">{{ $report['payroll_slips']->count() }} slip payroll bulan ini</span>
                    @endif
                </div>

                @if($report['payroll_slips']->count() > 1)
                    <table class="w-full text-xs mb-3">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="text-left py-1">Periode</th>
                                <th class="text-right py-1">Total</th>
                                <th class="text-right py-1">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['payroll_slips'] as $slip)
                                <tr class="border-t">
                                    <td class="py-1">{{ $slip->period_start->format('d M') }} - {{ $slip->period_end->format('d M') }}</td>
                                    <td class="text-right py-1">Rp{{ number_format($slip->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-right py-1">
                                        {{ ['draft' => 'Draft', 'unpaid' => 'Siap Bayar', 'paid' => 'Lunas'][$slip->status] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if($report['services']->isNotEmpty())
                    <table class="w-full text-xs mb-3">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="text-left py-1">Layanan</th>
                                <th class="text-right py-1">Jumlah Kendaraan</th>
                                <th class="text-right py-1">Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['services'] as $serviceName => $data)
                                <tr class="border-t">
                                    <td class="py-1">{{ $serviceName }}</td>
                                    <td class="text-right py-1">{{ $data['vehicle_count'] }}</td>
                                    <td class="text-right py-1">Rp{{ number_format($data['commission'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-gray-400 mb-3">Tidak ada pekerjaan tercatat pada periode ini.</p>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs bg-gray-50 rounded p-3">
                    <div>
                        <p class="text-gray-500">Total Kendaraan</p>
                        <p class="font-medium">{{ $report['total_vehicles'] }} unit</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Gaji Pokok</p>
                        <p class="font-medium">Rp{{ number_format($report['base_amount'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Komisi</p>
                        <p class="font-medium">Rp{{ number_format($report['total_commission'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tunjangan</p>
                        <p class="font-medium">Rp{{ number_format($report['allowance_total'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Gaji</p>
                        <p class="font-semibold text-teal-700">Rp{{ number_format($report['total_salary'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-sm">Belum ada data karyawan pada periode ini.</p>
        @endforelse
    </div>
</div>