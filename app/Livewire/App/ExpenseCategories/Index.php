<?php

namespace App\Livewire\App\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public bool $is_active = true;

    public function render()
    {
        $categories = ExpenseCategory::where('owner_id', Auth::user()->owner_id)
            ->withCount('expenses')
            ->latest()
            ->get();

        return view('livewire.app.expense-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $category = ExpenseCategory::where('owner_id', Auth::user()->owner_id)->findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            ExpenseCategory::where('owner_id', Auth::user()->owner_id)->findOrFail($this->editingId)->update($validated);
            $this->dispatch('notify', message: 'Kategori berhasil diperbarui.');
        } else {
            ExpenseCategory::create([...$validated, 'owner_id' => Auth::user()->owner_id]);
            $this->dispatch('notify', message: 'Kategori berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        ExpenseCategory::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Kategori berhasil dihapus.');
    }
}