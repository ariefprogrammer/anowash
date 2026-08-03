<?php

namespace App\Livewire\App\Pricing;

use App\Models\Outlet;
use App\Models\OutletService;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\ServicePriceHistory;
use App\Models\VehicleCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?string $outletId = null;

    public array $activeServices = [];   // [service_id => bool]
    public array $prices = [];           // ["{service_id}_{category_id}" => price]

    public function mount(): void
    {
        $firstOutlet = Outlet::where('owner_id', Auth::user()->owner_id)->first();
        $this->outletId = $firstOutlet?->id;

        $this->loadMatrix();
    }

    public function updatedOutletId(): void
    {
        $this->loadMatrix();
    }

    protected function loadMatrix(): void
    {
        $this->activeServices = [];
        $this->prices = [];

        if (! $this->outletId) {
            return;
        }

        $ownerId = Auth::user()->owner_id;

        // Pastikan outlet ini benar milik owner yang sedang login.
        $outlet = Outlet::where('owner_id', $ownerId)->find($this->outletId);
        if (! $outlet) {
            $this->outletId = null;
            return;
        }

        $services = Service::where('owner_id', $ownerId)->get();

        $existingOutletServices = OutletService::where('outlet_id', $this->outletId)
            ->pluck('is_active', 'service_id');

        foreach ($services as $service) {
            $this->activeServices[$service->id] = (bool) ($existingOutletServices[$service->id] ?? false);
        }

        $existingPrices = ServicePrice::where('outlet_id', $this->outletId)->get();

        foreach ($existingPrices as $price) {
            $key = "{$price->service_id}_{$price->vehicle_category_id}";
            $this->prices[$key] = $price->price;
        }
    }

    public function save(): void
    {
        $ownerId = Auth::user()->owner_id;

        $outlet = Outlet::where('owner_id', $ownerId)->find($this->outletId);
        abort_unless($outlet, 403);

        $services = Service::where('owner_id', $ownerId)->get();
        $categories = VehicleCategory::where('owner_id', $ownerId)->orWhereNull('owner_id')->get();

        foreach ($services as $service) {
            OutletService::updateOrCreate(
                ['outlet_id' => $this->outletId, 'service_id' => $service->id],
                ['is_active' => (bool) ($this->activeServices[$service->id] ?? false)]
            );

            foreach ($categories as $category) {
                $key = "{$service->id}_{$category->id}";
                $newPrice = $this->prices[$key] ?? null;

                if ($newPrice === null || $newPrice === '') {
                    continue;
                }

                $newPrice = (float) $newPrice;

                $existing = ServicePrice::where('outlet_id', $this->outletId)
                    ->where('service_id', $service->id)
                    ->where('vehicle_category_id', $category->id)
                    ->first();

                if ($existing && (float) $existing->price !== $newPrice) {
                    ServicePriceHistory::create([
                        'service_price_id' => $existing->id,
                        'old_price' => $existing->price,
                        'new_price' => $newPrice,
                        'changed_by' => Auth::id(),
                    ]);
                }

                ServicePrice::updateOrCreate(
                    [
                        'outlet_id' => $this->outletId,
                        'service_id' => $service->id,
                        'vehicle_category_id' => $category->id,
                    ],
                    ['price' => $newPrice, 'is_active' => true]
                );
            }
        }

        $this->dispatch('notify', message: 'Harga & layanan outlet berhasil disimpan.');
        $this->loadMatrix();
    }

    public function render()
    {
        $ownerId = Auth::user()->owner_id;

        return view('livewire.app.pricing.index', [
            'outlets' => Outlet::where('owner_id', $ownerId)->get(),
            'services' => Service::where('owner_id', $ownerId)->where('is_active', true)->get(),
            'categories' => VehicleCategory::where('owner_id', $ownerId)->orWhereNull('owner_id')->where('is_active', true)->get(),
        ]);
    }
}