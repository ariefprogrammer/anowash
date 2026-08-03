<?php

namespace App\Livewire\App\Reports;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\OrderItemWorker;
use App\Models\PayrollRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $period = '';
    public string $outletId = '';

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    protected function ownerOutletIds(): array
    {
        return Outlet::where('owner_id', Auth::user()->owner_id)->pluck('id')->all();
    }

    protected function selectedOutletIds(): array
    {
        $all = $this->ownerOutletIds();

        return $this->outletId ? array_intersect($all, [$this->outletId]) : $all;
    }

    public function render()
    {
        $ownerId = Auth::user()->owner_id;
        $outletIds = $this->selectedOutletIds();

        $periodStart = Carbon::parse($this->period.'-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // --- Ringkasan utama ---
        $vehicleCount = Order::whereIn('outlet_id', $outletIds)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $revenue = Order::whereIn('outlet_id', $outletIds)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$periodStart, $periodEnd])
            ->sum('total');

        $expenseTotal = Expense::whereIn('outlet_id', $outletIds)
            ->whereBetween('expense_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');

        $employeeIdsInOutlets = Employee::whereIn('outlet_id', $outletIds)->pluck('id');

        // Ambil SEMUA payroll record yang period_start-nya jatuh di bulan ini,
        // bisa lebih dari 1 baris per karyawan kalau siklusnya mingguan.
        $payrollRecords = PayrollRecord::whereIn('employee_id', $employeeIdsInOutlets)
            ->whereBetween('period_start', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        $payrollTotal = $payrollRecords->sum('total_amount');

        $netProfit = $revenue - $expenseTotal - $payrollTotal;

        // --- Detail per karyawan ---
        $employees = Employee::whereIn('outlet_id', $outletIds)->with('outlet')->orderBy('name')->get();

        $employeeReports = $employees->map(function ($employee) use ($periodStart, $periodEnd, $payrollRecords) {
            $assignments = OrderItemWorker::where('employee_id', $employee->id)
                ->whereHas('orderItem.order', function ($query) use ($periodStart, $periodEnd) {
                    $query->where('status', 'paid')->whereBetween('paid_at', [$periodStart, $periodEnd]);
                })
                ->with(['orderItem.order'])
                ->get();

            $serviceBreakdown = $assignments
                ->groupBy(fn ($a) => $a->orderItem->service_name_snapshot)
                ->map(fn ($group) => [
                    'vehicle_count' => $group->pluck('orderItem.order_id')->unique()->count(),
                    'commission' => $group->sum('commission_amount'),
                ]);

            $totalVehicles = $assignments->pluck('orderItem.order_id')->unique()->count();
            $totalCommission = $assignments->sum('commission_amount');

            // Semua slip payroll milik karyawan ini di bulan tersebut (bisa banyak kalau mingguan)
            $employeePayrolls = $payrollRecords->where('employee_id', $employee->id)->sortBy('period_start')->values();

            return [
                'employee' => $employee,
                'services' => $serviceBreakdown,
                'total_vehicles' => $totalVehicles,
                'total_commission' => $totalCommission,
                'payroll_slips' => $employeePayrolls,
                'allowance_total' => $employeePayrolls->sum('allowance_total'),
                'base_amount' => $employeePayrolls->sum('base_amount'),
                'commission_from_payroll' => $employeePayrolls->sum('commission_total'),
                'total_salary' => $employeePayrolls->sum('total_amount'),
            ];
        })->filter(fn ($r) => $r['total_vehicles'] > 0 || $r['total_salary'] > 0)->values();

        return view('livewire.app.reports.index', [
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'vehicleCount' => $vehicleCount,
            'revenue' => $revenue,
            'expenseTotal' => $expenseTotal,
            'payrollTotal' => $payrollTotal,
            'netProfit' => $netProfit,
            'employeeReports' => $employeeReports,
            'outlets' => Outlet::whereIn('id', $this->ownerOutletIds())->get(),
        ]);
    }
}