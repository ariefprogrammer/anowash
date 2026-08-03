<?php

namespace App\Livewire\App\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingId = null;

    public ?string $outlet_id = null;
    public string $expense_category_id = '';
    public string $title = '';
    public string $description = '';
    public string $amount = '';
    public string $expense_date = '';

    public string $filterOutletId = '';
    public string $filterMonth = '';

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
    }

    protected function accessibleOutletIds(): array
    {
        $user = Auth::user();

        return $user->role === 'admin_outlet'
            ? [$user->outlet_id]
            : Outlet::where('owner_id', $user->owner_id)->pluck('id')->all();
    }

    public function render()
    {
        $outletIds = $this->accessibleOutletIds();

        $query = Expense::whereIn('outlet_id', $outletIds)->with(['outlet', 'category'])->latest('expense_date');

        if ($this->filterOutletId) {
            $query->where('outlet_id', $this->filterOutletId);
        }

        if ($this->filterMonth) {
            $query->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$this->filterMonth]);
        }

        $totalFiltered = (clone $query)->sum('amount');

        return view('livewire.app.expenses.index', [
            'expenses' => $query->paginate(15),
            'totalFiltered' => $totalFiltered,
            'outlets' => Outlet::whereIn('id', $outletIds)->get(),
            'categories' => ExpenseCategory::where('owner_id', Auth::user()->owner_id)->where('is_active', true)->get(),
        ]);
    }

    public function openCreateModal(): void
    {
        $user = Auth::user();

        $this->reset(['editingId', 'expense_category_id', 'title', 'description', 'amount']);
        $this->outlet_id = $user->role === 'admin_outlet' ? $user->outlet_id : ($this->accessibleOutletIds()[0] ?? null);
        $this->expense_date = now()->format('Y-m-d'); // default hari ini, tetap bisa diubah user
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $expense = Expense::whereIn('outlet_id', $this->accessibleOutletIds())->findOrFail($id);

        $this->editingId = $expense->id;
        $this->outlet_id = $expense->outlet_id;
        $this->expense_category_id = $expense->expense_category_id ? (string) $expense->expense_category_id : '';
        $this->title = $expense->title;
        $this->description = $expense->description ?? '';
        $this->amount = (string) $expense->amount;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function save(): void
    {
        $accessibleOutletIds = $this->accessibleOutletIds();

        $validated = $this->validate([
            'outlet_id' => ['required', \Illuminate\Validation\Rule::in($accessibleOutletIds)],
            'expense_category_id' => ['nullable', \Illuminate\Validation\Rule::exists('expense_categories', 'id')->where('owner_id', Auth::user()->owner_id)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
        ]);

        $validated['expense_category_id'] = $validated['expense_category_id'] ?: null;

        if ($this->editingId) {
            Expense::whereIn('outlet_id', $accessibleOutletIds)->findOrFail($this->editingId)->update($validated);
            $this->dispatch('notify', message: 'Pengeluaran berhasil diperbarui.');
        } else {
            Expense::create([...$validated, 'created_by' => Auth::id()]);
            $this->dispatch('notify', message: 'Pengeluaran berhasil dicatat.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Expense::whereIn('outlet_id', $this->accessibleOutletIds())->findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Pengeluaran berhasil dihapus.');
    }
}