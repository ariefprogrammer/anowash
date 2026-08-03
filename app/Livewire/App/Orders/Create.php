<?php

namespace App\Livewire\App\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\OutletService;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public ?string $outletId = null;

    public string $plateNumber = '';
    public ?int $vehicleId = null;
    public ?int $vehicleCategoryId = null;
    public string $vehicleBrand = '';
    public string $vehicleModel = '';
    public bool $vehicleFound = false;

    public string $customerName = '';
    public string $customerPhone = '';

    public array $items = [];
    public string $discount = '0';
    public string $notes = '';
    public string $paymentMethod = 'Cash';

    public bool $showCustomerDetails = false;
    
    public bool $payNow = false;
    public bool $showPayModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->outletId = $user->role === 'admin_outlet'
            ? $user->outlet_id
            : Outlet::where('owner_id', $user->owner_id)->where('is_active', true)->first()?->id;

        $this->addItem();
    }

    public function toggleCustomerDetails(): void
    {
        $this->showCustomerDetails = ! $this->showCustomerDetails;
    }

    public function updatedOutletId(): void
    {
        $this->items = [];
        $this->addItem();
    }

    public function updatedPlateNumber(): void
    {
        $ownerId = Auth::user()->owner_id;
        $plate = strtoupper(trim($this->plateNumber));

        $vehicle = Vehicle::where('owner_id', $ownerId)->where('plate_number', $plate)->first();

        if ($vehicle) {
            $this->vehicleId = $vehicle->id;
            $this->vehicleCategoryId = $vehicle->vehicle_category_id;
            $this->vehicleBrand = $vehicle->brand ?? '';
            $this->vehicleModel = $vehicle->model ?? '';
            $this->vehicleFound = true;

            if ($vehicle->brand || $vehicle->model) {
                $this->showCustomerDetails = true;
            }

            if ($vehicle->customer) {
                $this->customerName = $vehicle->customer->name;
                $this->customerPhone = $vehicle->customer->phone ?? '';
                $this->showCustomerDetails = true;
            }

            $this->recalculateAllPrices();
        } else {
            $this->vehicleId = null;
            $this->vehicleFound = false;
        }
    }

    public function updatedVehicleCategoryId(): void
    {
        $this->recalculateAllPrices();
    }

    public function addItem(): void
    {
        $this->items[] = ['service_id' => null, 'price' => '0', 'quantity' => 1];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function openPayModal(): void
    {
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
    }

    public function confirmPay(): void
    {
        $this->payNow = true;
        $this->showPayModal = false;
        $this->save();
    }

    public function onServiceChange(int $index): void
    {
        $this->fillPriceForItem($index);
    }

    protected function fillPriceForItem(int $index): void
    {
        if (! $this->outletId || ! $this->vehicleCategoryId || empty($this->items[$index]['service_id'])) {
            return;
        }

        $price = ServicePrice::where('outlet_id', $this->outletId)
            ->where('service_id', $this->items[$index]['service_id'])
            ->where('vehicle_category_id', $this->vehicleCategoryId)
            ->value('price');

        $this->items[$index]['price'] = $price !== null ? (string) $price : '0';
    }

    protected function recalculateAllPrices(): void
    {
        foreach (array_keys($this->items) as $index) {
            $this->fillPriceForItem($index);
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($item) => (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1));
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - (float) ($this->discount ?: 0));
    }

    public function availableServices()
    {
        if (! $this->outletId) {
            return collect();
        }

        $serviceIds = OutletService::where('outlet_id', $this->outletId)->where('is_active', true)->pluck('service_id');

        return Service::whereIn('id', $serviceIds)->where('is_active', true)->get();
    }

    public function save(): void
    {
        $user = Auth::user();
        $ownerId = $user->owner_id;

        $outlet = Outlet::where('owner_id', $ownerId)->findOrFail($this->outletId);

        if ($user->role === 'admin_outlet' && $outlet->id !== $user->outlet_id) {
            abort(403);
        }

        $this->validate([
            'plateNumber' => ['required', 'string', 'max:20'],
            'vehicleCategoryId' => ['required', 'exists:vehicle_categories,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($ownerId, $outlet, $user) {
            $vehicleId = $this->vehicleId;

            if (! $vehicleId) {
                $customerId = null;

                if (filled($this->customerPhone) || filled($this->customerName)) {
                    $customer = Customer::firstOrCreate(
                        ['owner_id' => $ownerId, 'phone' => $this->customerPhone ?: null],
                        ['name' => $this->customerName ?: 'Pelanggan']
                    );
                    $customerId = $customer->id;
                }

                $vehicle = Vehicle::create([
                    'owner_id' => $ownerId,
                    'customer_id' => $customerId,
                    'plate_number' => strtoupper(trim($this->plateNumber)),
                    'vehicle_category_id' => $this->vehicleCategoryId,
                    'brand' => $this->vehicleBrand ?: null,
                    'model' => $this->vehicleModel ?: null,
                ]);

                $vehicleId = $vehicle->id;
            }

            $order = Order::create([
                'outlet_id' => $outlet->id,
                'vehicle_id' => $vehicleId,
                'plate_number_snapshot' => strtoupper(trim($this->plateNumber)),
                'vehicle_category_id' => $this->vehicleCategoryId,
                'customer_name_snapshot' => $this->customerName ?: null,
                'customer_phone_snapshot' => $this->customerPhone ?: null,
                'status' => 'pending',
                'payment_status' => $this->payNow ? 'paid' : 'unpaid',
                'subtotal' => $this->subtotal,
                'discount' => $this->payNow ? (float) ($this->discount ?: 0) : 0,
                'total' => $this->total,
                'payment_method' => $this->payNow ? $this->paymentMethod : null,
                'paid_at' => $this->payNow ? now() : null,
                'created_by' => $user->id,
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->items as $item) {
                $service = Service::find($item['service_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'service_name_snapshot' => $service->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['price'] * $item['quantity'],
                ]);
            }
        });

        $this->dispatch('notify', message: $this->payNow ? 'Order berhasil dibuat & ditandai lunas.' : 'Order berhasil dibuat.');
        $this->redirect(route('app.orders'), navigate: false);
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.app.orders.create', [
            'outlets' => $user->role === 'owner'
                ? Outlet::where('owner_id', $user->owner_id)->where('is_active', true)->get()
                : collect(),
            'categories' => VehicleCategory::where('owner_id', $user->owner_id)->orWhereNull('owner_id')->where('is_active', true)->get(),
            'services' => $this->availableServices(),
        ]);
    }
}