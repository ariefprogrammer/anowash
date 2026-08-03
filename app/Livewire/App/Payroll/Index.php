<?php

namespace App\Livewire\App\Payroll;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\OrderItemWorker;
use App\Models\PayrollRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showGenerateModal = false;
    public bool $showEditModal = false;

    public ?string $outletId = null;
    public string $periodStart = '';
    public string $periodEnd = '';

    public string $statusFilter = '';

    // --- state untuk edit payroll ---
    public ?int $editingPayrollId = null;
    public string $editType = 'commission';
    public string $editBaseAmount = '0';
    public string $editCommissionTotal = '0';
    public array $editDetails = []; // [['id' => ?, 'name' => '', 'amount' => '']]

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->format('Y-m-d');
        $this->periodEnd = now()->endOfMonth()->format('Y-m-d');
    }

    public function openGenerateModal(): void
    {
        $this->showGenerateModal = true;
    }

    public function generate(): void
    {
        $ownerId = Auth::user()->owner_id;

        $validated = $this->validate([
            'outletId' => ['required', \Illuminate\Validation\Rule::exists('outlets', 'id')->where('owner_id', $ownerId)],
            'periodStart' => ['required', 'date'],
            'periodEnd' => ['required', 'date', 'after_or_equal:periodStart'],
        ]);

        $employees = Employee::where('owner_id', $ownerId)
            ->where('outlet_id', $validated['outletId'])
            ->where('is_active', true)
            ->get();

        if ($employees->isEmpty()) {
            $this->dispatch('notify', message: 'Tidak ada karyawan aktif di outlet ini.', type: 'error');
            return;
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($employees, $validated, &$created, &$skipped) {
            foreach ($employees as $employee) {
                $alreadyExists = PayrollRecord::where('employee_id', $employee->id)
                    ->where('period_start', $validated['periodStart'])
                    ->where('period_end', $validated['periodEnd'])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                $allowances = $employee->allowanceTypes()->where('is_active', true)->get();
                $allowanceTotal = $allowances->sum('amount');

                if ($employee->payroll_type === 'flat') {
                    $baseAmount = $employee->flat_salary ?? 0;
                    $commissionTotal = 0;
                } else {
                    $baseAmount = 0;
                    $commissionTotal = OrderItemWorker::where('employee_id', $employee->id)
                        ->whereHas('orderItem.order', function ($query) use ($validated) {
                            $query->where('status', 'paid')
                                ->whereBetween('paid_at', [$validated['periodStart'].' 00:00:00', $validated['periodEnd'].' 23:59:59']);
                        })
                        ->sum('commission_amount');
                }

                $record = PayrollRecord::create([
                    'employee_id' => $employee->id,
                    'period_start' => $validated['periodStart'],
                    'period_end' => $validated['periodEnd'],
                    'type' => $employee->payroll_type,
                    'base_amount' => $baseAmount,
                    'commission_total' => $commissionTotal,
                    'allowance_total' => $allowanceTotal,
                    'total_amount' => $baseAmount + $commissionTotal + $allowanceTotal,
                    'status' => 'draft',
                ]);

                foreach ($allowances as $allowance) {
                    $record->details()->create([
                        'allowance_type_id' => $allowance->id,
                        'name_snapshot' => $allowance->name,
                        'amount' => $allowance->amount,
                    ]);
                }

                $created++;
            }
        });

        $message = "{$created} slip payroll berhasil dibuat.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati (sudah ada untuk periode ini).";
        }

        $this->dispatch('notify', message: $message);
        $this->showGenerateModal = false;
    }

    public function openEditPayrollModal(int $id): void
    {
        $record = $this->authorizedRecord($id);

        if ($record->status === 'paid') {
            $this->dispatch('notify', message: 'Payroll yang sudah dibayar tidak bisa diedit.', type: 'error');
            return;
        }

        $this->editingPayrollId = $record->id;
        $this->editType = $record->type;
        $this->editBaseAmount = (string) $record->base_amount;
        $this->editCommissionTotal = (string) $record->commission_total;

        $this->editDetails = $record->details->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name_snapshot,
            'amount' => (string) $d->amount,
        ])->toArray();

        $this->showEditModal = true;
    }

    public function addEditDetailRow(): void
    {
        $this->editDetails[] = ['id' => null, 'name' => '', 'amount' => '0'];
    }

    public function removeEditDetailRow(int $index): void
    {
        unset($this->editDetails[$index]);
        $this->editDetails = array_values($this->editDetails);
    }

    public function getEditTotalProperty(): float
    {
        $detailSum = collect($this->editDetails)->sum(fn ($d) => (float) ($d['amount'] ?: 0));

        return (float) ($this->editBaseAmount ?: 0) + (float) ($this->editCommissionTotal ?: 0) + $detailSum;
    }

    public function savePayroll(): void
    {
        $record = $this->authorizedRecord($this->editingPayrollId);

        if ($record->status === 'paid') {
            $this->dispatch('notify', message: 'Payroll yang sudah dibayar tidak bisa diedit.', type: 'error');
            $this->showEditModal = false;
            return;
        }

        $this->validate([
            'editBaseAmount' => ['required', 'numeric', 'min:0'],
            'editCommissionTotal' => ['required', 'numeric', 'min:0'],
            'editDetails.*.name' => ['required', 'string', 'max:100'],
            'editDetails.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($record) {
            $keepIds = [];

            foreach ($this->editDetails as $detail) {
                if ($detail['id']) {
                    $record->details()->where('id', $detail['id'])->update([
                        'name_snapshot' => $detail['name'],
                        'amount' => $detail['amount'],
                    ]);
                    $keepIds[] = $detail['id'];
                } else {
                    $new = $record->details()->create([
                        'allowance_type_id' => null,
                        'name_snapshot' => $detail['name'],
                        'amount' => $detail['amount'],
                    ]);
                    $keepIds[] = $new->id;
                }
            }

            // Hapus baris detail yang tadinya ada tapi sudah di-remove dari form
            $record->details()->whereNotIn('id', $keepIds)->delete();

            $allowanceTotal = collect($this->editDetails)->sum(fn ($d) => (float) $d['amount']);

            $record->update([
                'base_amount' => $this->editBaseAmount,
                'commission_total' => $this->editCommissionTotal,
                'allowance_total' => $allowanceTotal,
                'total_amount' => (float) $this->editBaseAmount + (float) $this->editCommissionTotal + $allowanceTotal,
            ]);
        });

        $this->dispatch('notify', message: 'Payroll berhasil disesuaikan.');
        $this->showEditModal = false;
    }

    public function markUnpaid(int $id): void
    {
        $this->authorizedRecord($id)->update(['status' => 'unpaid']);
        $this->dispatch('notify', message: 'Payroll ditandai siap dibayar.');
    }

    public function markPaid(int $id): void
    {
        $this->authorizedRecord($id)->update(['status' => 'paid', 'paid_at' => now()]);
        $this->dispatch('notify', message: 'Payroll ditandai sudah dibayar.');
    }

    protected function authorizedRecord(int $id): PayrollRecord
    {
        $ownerId = Auth::user()->owner_id;

        return PayrollRecord::whereHas('employee', fn ($q) => $q->where('owner_id', $ownerId))
            ->with('details')
            ->findOrFail($id);
    }

    public function render()
    {
        $ownerId = Auth::user()->owner_id;

        $query = PayrollRecord::whereHas('employee', fn ($q) => $q->where('owner_id', $ownerId))
            ->with(['employee.outlet', 'details'])
            ->latest('period_start');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.app.payroll.index', [
            'records' => $query->paginate(15),
            'outlets' => Outlet::where('owner_id', $ownerId)->where('is_active', true)->get(),
        ]);
    }
}