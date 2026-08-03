<?php

namespace App\Livewire\App\VehicleCategories;

use App\Models\VehicleCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function render()
    {
        $owner = Auth::user()->owner;

        $categories = VehicleCategory::where('owner_id', $owner->id)
            ->orWhereNull('owner_id')
            ->orderByRaw('owner_id IS NULL')
            ->latest()
            ->get();

        return view('livewire.app.vehicle-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $category = VehicleCategory::where('owner_id', Auth::user()->owner_id)->findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            VehicleCategory::where('owner_id', Auth::user()->owner_id)
                ->findOrFail($this->editingId)
                ->update($validated);

            $this->dispatch('notify', message: 'Kategori berhasil diperbarui.');
        } else {
            VehicleCategory::create([
                ...$validated,
                'owner_id' => Auth::user()->owner_id,
            ]);

            $this->dispatch('notify', message: 'Kategori berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        // Kategori global (owner_id NULL) tidak boleh dihapus owner mana pun.
        VehicleCategory::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();

        $this->dispatch('notify', message: 'Kategori berhasil dihapus.');
    }
}