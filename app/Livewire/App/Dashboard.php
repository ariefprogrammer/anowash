<?php

namespace App\Livewire\App;

use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $outletIds = $user->role === 'admin_outlet'
            ? [$user->outlet_id]
            : Outlet::where('owner_id', $user->owner_id)->pluck('id')->all();

        $revenueQuery = fn () => Order::whereIn('outlet_id', $outletIds)->where('status', 'paid');
        $vehicleQuery = fn () => Order::whereIn('outlet_id', $outletIds)->where('status', '!=', 'cancelled');

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::today()->startOfMonth();

        $revenueToday = (clone $revenueQuery())->whereDate('paid_at', $today)->sum('total');
        $revenueYesterday = (clone $revenueQuery())->whereDate('paid_at', $yesterday)->sum('total');
        $revenueThisMonth = (clone $revenueQuery())
            ->whereBetween('paid_at', [$startOfMonth->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->sum('total');

        $vehiclesToday = (clone $vehicleQuery())->whereDate('created_at', $today)->count();
        $vehiclesYesterday = (clone $vehicleQuery())->whereDate('created_at', $yesterday)->count();
        $vehiclesThisMonth = (clone $vehicleQuery())
            ->whereBetween('created_at', [$startOfMonth->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->count();

        // Grafik 30 hari terakhir, termasuk hari ini
        $rangeStart = Carbon::today()->subDays(29);

        $dailyRevenues = (clone $revenueQuery())
            ->whereBetween('paid_at', [$rangeStart->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(paid_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartLabels = [];
        $chartData = [];

        for ($date = $rangeStart->copy(); $date->lte($today); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartData[] = (float) ($dailyRevenues[$key] ?? 0);
        }

        return view('livewire.app.dashboard', [
            'revenueToday' => $revenueToday,
            'revenueYesterday' => $revenueYesterday,
            'revenueThisMonth' => $revenueThisMonth,
            'vehiclesToday' => $vehiclesToday,
            'vehiclesYesterday' => $vehiclesYesterday,
            'vehiclesThisMonth' => $vehiclesThisMonth,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}