<?php

namespace App\Livewire\App\Services;

use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $category = '';
    public string $description = '';
    public ?int $estimated_duration_min = null;
    public bool $is_active = true;

    public function render()
    {
        $services = Service::where('owner_id', Auth::user()->owner_id)->latest()->get();

        return view('livewire.app.services.index', [
            'services' => $services,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'category', 'description', 'estimated_duration_min']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $service = Service::where('owner_id', Auth::user()->owner_id)->findOrFail($id);

        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->category = $service->category ?? '';
        $this->description = $service->description ?? '';
        $this->estimated_duration_min = $service->estimated_duration_min;
        $this->is_active = $service->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'estimated_duration_min' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            Service::where('owner_id', Auth::user()->owner_id)
                ->findOrFail($this->editingId)
                ->update($validated);

            $this->dispatch('notify', message: 'Layanan berhasil diperbarui.');
        } else {
            Service::create([
                ...$validated,
                'owner_id' => Auth::user()->owner_id,
            ]);

            $this->dispatch('notify', message: 'Layanan berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        Service::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();

        $this->dispatch('notify', message: 'Layanan berhasil dihapus.');
    }
}