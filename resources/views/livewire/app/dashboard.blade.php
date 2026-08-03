<div class="p-6">
    <h1 class="text-2xl font-semibold mb-1">Dashboard</h1>
    <p class="text-gray-600 mb-6">
        Selamat datang, {{ auth()->user()->name }} ({{ auth()->user()->role }})
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Omset Hari Ini</p>
            <p class="text-2xl font-semibold mt-1">Rp{{ number_format($revenueToday, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Omset Kemarin</p>
            <p class="text-2xl font-semibold mt-1">Rp{{ number_format($revenueYesterday, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Omset Bulan Ini</p>
            <p class="text-2xl font-semibold mt-1">Rp{{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Kendaraan Hari Ini</p>
            <p class="text-2xl font-semibold mt-1">{{ $vehiclesToday }} unit</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Kendaraan Kemarin</p>
            <p class="text-2xl font-semibold mt-1">{{ $vehiclesYesterday }} unit</p>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Kendaraan Bulan Ini</p>
            <p class="text-2xl font-semibold mt-1">{{ $vehiclesThisMonth }} unit</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-sm text-gray-500 mb-4">Pendapatan 30 Hari Terakhir</p>
        <div wire:ignore x-data x-init="
            let ctx = $refs.revenueChart.getContext('2d');
            let chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($chartLabels),
                    datasets: [{
                        label: 'Omset',
                        data: @js($chartData),
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return 'Rp' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            Livewire.on('refreshChart', (data) => {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.values;
                chart.update();
            });
        ">
            <canvas x-ref="revenueChart" height="80"></canvas>
        </div>
    </div>
</div>