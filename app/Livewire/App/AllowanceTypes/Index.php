<?php

namespace App\Livewire\App\AllowanceTypes;

use App\Models\AllowanceType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $amount = '';
    public bool $is_active = true;

    public function render()
    {
        $types = AllowanceType::where('owner_id', Auth::user()->owner_id)
            ->withCount('employees')
            ->latest()
            ->get();

        return view('livewire.app.allowance-types.index', [
            'types' => $types,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'amount']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $type = AllowanceType::where('owner_id', Auth::user()->owner_id)->findOrFail($id);

        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->amount = (string) $type->amount;
        $this->is_active = $type->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            AllowanceType::where('owner_id', Auth::user()->owner_id)->findOrFail($this->editingId)->update($validated);
            $this->dispatch('notify', message: 'Jenis tunjangan berhasil diperbarui.');
        } else {
            AllowanceType::create([...$validated, 'owner_id' => Auth::user()->owner_id]);
            $this->dispatch('notify', message: 'Jenis tunjangan berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        AllowanceType::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Jenis tunjangan berhasil dihapus.');
    }
}