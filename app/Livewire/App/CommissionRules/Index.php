<?php

namespace App\Livewire\App\CommissionRules;

use App\Models\CommissionRule;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $service_id = '';
    public string $basis = 'percentage';
    public string $value = '';

    public function render()
    {
        $ownerId = Auth::user()->owner_id;

        $rules = CommissionRule::where('owner_id', $ownerId)->with('service')->orderByRaw('service_id IS NOT NULL')->get();
        $services = Service::where('owner_id', $ownerId)->where('is_active', true)->get();

        return view('livewire.app.commission-rules.index', [
            'rules' => $rules,
            'services' => $services,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'service_id', 'value']);
        $this->basis = 'percentage';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $rule = CommissionRule::where('owner_id', Auth::user()->owner_id)->findOrFail($id);

        $this->editingId = $rule->id;
        $this->service_id = $rule->service_id ? (string) $rule->service_id : '';
        $this->basis = $rule->basis;
        $this->value = (string) $rule->value;
        $this->showModal = true;
    }

    public function save(): void
    {
        $ownerId = Auth::user()->owner_id;

        $validated = $this->validate([
            'service_id' => [
                'nullable',
                Rule::exists('services', 'id')->where('owner_id', $ownerId),
                Rule::unique('commission_rules', 'service_id')
                    ->where('owner_id', $ownerId)
                    ->ignore($this->editingId),
            ],
            'basis' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        // service_id kosong = aturan default. Rule::unique di atas tidak
        // otomatis handle NULL dengan benar untuk kasus "hanya boleh 1 default",
        // jadi kita cek manual di sini.
        $serviceId = $validated['service_id'] ?: null;

        if ($serviceId === null) {
            $defaultExists = CommissionRule::where('owner_id', $ownerId)
                ->whereNull('service_id')
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($defaultExists) {
                $this->addError('service_id', 'Aturan default (semua layanan) sudah ada. Edit yang sudah ada, atau pilih layanan spesifik.');
                return;
            }
        }

        if ($this->basis === 'percentage' && $validated['value'] > 100) {
            $this->addError('value', 'Persentase tidak boleh lebih dari 100.');
            return;
        }

        $payload = [
            'service_id' => $serviceId,
            'basis' => $validated['basis'],
            'value' => $validated['value'],
        ];

        if ($this->editingId) {
            CommissionRule::where('owner_id', $ownerId)->findOrFail($this->editingId)->update($payload);
            $this->dispatch('notify', message: 'Aturan komisi berhasil diperbarui.');
        } else {
            CommissionRule::create([...$payload, 'owner_id' => $ownerId]);
            $this->dispatch('notify', message: 'Aturan komisi berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        CommissionRule::where('owner_id', Auth::user()->owner_id)->findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Aturan komisi berhasil dihapus.');
    }
}