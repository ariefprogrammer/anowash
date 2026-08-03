<?php

namespace App\Livewire\App\Employees;

use App\Models\Employee;
use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\AllowanceType;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $phone = '';
    public string $address = '';
    public ?string $outlet_id = null;
    public ?string $join_date = null;
    public string $payroll_type = 'commission';
    public string $flat_salary = '';
    public bool $is_active = true;
    public array $selectedAllowanceTypes = [];

    public function render()
    {
        $owner = Auth::user()->owner;

        $employees = Employee::where('owner_id', $owner->id)->with('outlet')->latest()->get();
        $outlets = Outlet::where('owner_id', $owner->id)->where('is_active', true)->get();
        $allowanceTypes = AllowanceType::where('owner_id', $owner->id)->where('is_active', true)->get();

        return view('livewire.app.employees.index', [
            'employees' => $employees,
            'outlets' => $outlets,
            'allowanceTypes' => $allowanceTypes,
        ]);
    }

    public function openCreateModal(): void
    {
        $owner = Auth::user()->owner;

        if ($owner->outlets()->count() === 0) {
            $this->dispatch('notify', message: 'Buat outlet terlebih dahulu sebelum menambah karyawan.', type: 'error');
            return;
        }

        $this->reset(['editingId', 'name', 'phone', 'address', 'outlet_id', 'join_date', 'flat_salary', 'selectedAllowanceTypes']);
        $this->payroll_type = 'commission';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $employee = Employee::where('owner_id', Auth::user()->owner_id)->with('allowanceTypes')->findOrFail($id);

        $this->editingId = $employee->id;
        $this->name = $employee->name;
        $this->phone = $employee->phone ?? '';
        $this->address = $employee->address ?? '';
        $this->outlet_id = $employee->outlet_id;
        $this->join_date = $employee->join_date?->format('Y-m-d');
        $this->payroll_type = $employee->payroll_type;
        $this->flat_salary = $employee->flat_salary !== null ? (string) $employee->flat_salary : '';
        $this->is_active = $employee->is_active;
        $this->selectedAllowanceTypes = $employee->allowanceTypes->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $owner = Auth::user()->owner;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'outlet_id' => ['required', \Illuminate\Validation\Rule::exists('outlets', 'id')->where('owner_id', $owner->id)],
            'join_date' => ['nullable', 'date'],
            'payroll_type' => ['required', 'in:flat,commission'],
            'flat_salary' => [$this->payroll_type === 'flat' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['flat_salary'] = $this->payroll_type === 'flat' ? $validated['flat_salary'] : null;

        if ($this->editingId) {
            $employee = Employee::where('owner_id', $owner->id)->findOrFail($this->editingId);
            $employee->update($validated);
            $this->dispatch('notify', message: 'Data karyawan berhasil diperbarui.');
        } else {
            $employee = Employee::create([...$validated, 'owner_id' => $owner->id]);
            $this->dispatch('notify', message: 'Karyawan berhasil ditambahkan.');
        }

        // Pastikan hanya allowance type milik owner ini yang bisa di-sync,
        // mencegah manipulasi ID lewat request langsung.
        $validAllowanceIds = AllowanceType::where('owner_id', $owner->id)
            ->whereIn('id', $this->selectedAllowanceTypes)
            ->pluck('id');

        $employee->allowanceTypes()->sync($validAllowanceIds);

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Employee::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Karyawan berhasil dihapus.');
    }
}