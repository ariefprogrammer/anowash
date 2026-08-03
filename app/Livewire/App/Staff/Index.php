<?php

namespace App\Livewire\App\Staff;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public ?string $outlet_id = null;
    public bool $is_active = true;

    public function render()
    {
        $owner = Auth::user()->owner;

        $staff = User::where('owner_id', $owner->id)
            ->where('role', 'admin_outlet')
            ->with('outlet')
            ->latest()
            ->get();

        $outlets = Outlet::where('owner_id', $owner->id)
            ->where('is_active', true)
            ->get();

        return view('livewire.app.staff.index', [
            'staff' => $staff,
            'outlets' => $outlets,
        ]);
    }

    public function openCreateModal(): void
    {
        $owner = Auth::user()->owner;

        if ($owner->outlets()->count() === 0) {
            $this->dispatch('notify', message: 'Buat outlet terlebih dahulu sebelum menambah staff.', type: 'error');
            return;
        }

        $this->reset(['editingId', 'name', 'email', 'phone', 'password', 'outlet_id']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(string $userId): void
    {
        $owner = Auth::user()->owner;

        $user = User::where('owner_id', $owner->id)
            ->where('role', 'admin_outlet')
            ->findOrFail($userId);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->password = '';
        $this->outlet_id = $user->outlet_id;
        $this->is_active = $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $owner = Auth::user()->owner;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
            'outlet_id' => [
                'required',
                Rule::exists('outlets', 'id')->where('owner_id', $owner->id),
            ],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            $user = User::where('owner_id', $owner->id)
                ->where('role', 'admin_outlet')
                ->findOrFail($this->editingId);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'outlet_id' => $validated['outlet_id'],
                'is_active' => $validated['is_active'],
                ...(filled($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
            ]);

            $this->dispatch('notify', message: 'Data staff berhasil diperbarui.');
        } else {
            User::create([
                'owner_id' => $owner->id,
                'outlet_id' => $validated['outlet_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin_outlet',
                'is_active' => true,
            ]);

            $this->dispatch('notify', message: 'Staff berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function toggleActive(string $userId): void
    {
        $owner = Auth::user()->owner;

        $user = User::where('owner_id', $owner->id)
            ->where('role', 'admin_outlet')
            ->findOrFail($userId);

        $user->update(['is_active' => ! $user->is_active]);

        $this->dispatch('notify', message: $user->is_active ? 'Staff diaktifkan.' : 'Staff dinonaktifkan.');
    }
}