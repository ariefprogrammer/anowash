<?php

namespace App\Livewire\App\Orders;

use App\Models\CommissionRule;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItemWorker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Workers extends Component
{
    public Order $order;

    // [item_id => [ ['employee_id' => ?, 'split_type' => 'equal', 'split_value' => null], ... ] ]
    public array $assignments = [];

    // [item_id => bool] — default true: semua baris di item ini dipaksa pakai split_type yang sama
    public array $uniformSplitType = [];

    public function mount(Order $order): void
    {
        $user = Auth::user();

        $outletIds = $user->role === 'admin_outlet'
            ? [$user->outlet_id]
            : \App\Models\Outlet::where('owner_id', $user->owner_id)->pluck('id')->all();

        abort_unless(in_array($order->outlet_id, $outletIds, true), 403);
        abort_unless(in_array($order->status, ['in_progress', 'completed', 'paid'], true), 403, 'Order harus dikerjakan (in_progress) terlebih dahulu.');

        $this->order = $order->load('items.workers', 'outlet');

        foreach ($this->order->items as $item) {
            $rows = $item->workers->map(fn ($w) => [
                'id' => $w->id,
                'employee_id' => $w->employee_id,
                'split_type' => $w->split_type,
                'split_value' => $w->split_value,
            ])->toArray();

            $this->assignments[$item->id] = $rows ?: [
                ['id' => null, 'employee_id' => null, 'split_type' => 'equal', 'split_value' => null],
            ];

            $this->uniformSplitType[$item->id] = true;
        }
    }

    public function addRow(int $itemId): void
    {
        // Baris baru ikut tipe yang sedang berlaku di item ini (biar konsisten dgn baris lain)
        $currentType = $this->assignments[$itemId][0]['split_type'] ?? 'equal';

        $this->assignments[$itemId][] = ['id' => null, 'employee_id' => null, 'split_type' => $currentType, 'split_value' => null];

        $this->recalculatePreview();
    }

    public function removeRow(int $itemId, int $rowIndex): void
    {
        unset($this->assignments[$itemId][$rowIndex]);
        $this->assignments[$itemId] = array_values($this->assignments[$itemId]);

        if (empty($this->assignments[$itemId])) {
            $this->assignments[$itemId][] = ['id' => null, 'employee_id' => null, 'split_type' => 'equal', 'split_value' => null];
        }

        $this->recalculatePreview();
    }

    public function employees()
    {
        return Employee::where('outlet_id', $this->order->outlet_id)->where('is_active', true)->get();
    }

    protected function commissionBaseForItem($item): float
    {
        $ownerId = $this->order->outlet->owner_id ?? null;

        $rule = CommissionRule::where('owner_id', $ownerId)
            ->where('service_id', $item->service_id)
            ->first();

        if (! $rule) {
            $rule = CommissionRule::where('owner_id', $ownerId)
                ->whereNull('service_id')
                ->first();
        }

        if (! $rule) {
            return 0;
        }

        return $rule->basis === 'percentage'
            ? ($item->line_total * (float) $rule->value / 100)
            : (float) $rule->value;
    }

    public function getPreviewProperty(): array
    {
        $preview = [];

        foreach ($this->order->items as $item) {
            $base = $this->commissionBaseForItem($item);
            $rows = $this->assignments[$item->id] ?? [];
            $validRows = collect($rows)->filter(fn ($r) => filled($r['employee_id']));
            $count = $validRows->count();

            $itemPreview = [];

            foreach ($rows as $i => $row) {
                if (blank($row['employee_id'])) {
                    $itemPreview[$i] = 0;
                    continue;
                }

                $itemPreview[$i] = match ($row['split_type']) {
                    'equal' => $count > 0 ? round($base / $count, 2) : 0,
                    'percentage' => round($base * (float) ($row['split_value'] ?? 0) / 100, 2),
                    'fixed_amount' => (float) ($row['split_value'] ?? 0),
                    default => 0,
                };
            }

            $preview[$item->id] = ['base' => $base, 'rows' => $itemPreview];
        }

        return $preview;
    }

    public function updated($property, $value)
    {
        // Kalau yang berubah adalah split_type salah satu row
        if (preg_match('/^assignments\.(\d+)\.(\d+)\.split_type$/', $property, $m)) {
            $itemId = (int) $m[1];

            if ($this->uniformSplitType[$itemId] ?? true) {
                // Seragamkan semua row lain di item yang sama (perilaku default)
                foreach ($this->assignments[$itemId] as $i => &$row) {
                    $row['split_type'] = $value;
                    $row['split_value'] = null; // reset, karena makna nominal beda per tipe
                }
                unset($row);
            } else {
                // Mode manual: hanya reset split_value baris yang diubah saja
                foreach ($this->assignments[$itemId] as $i => &$row) {
                    if ($row['split_type'] === $value && array_key_exists('split_value', $row)) {
                        // no-op untuk baris lain
                    }
                }
                unset($row);
            }
        }

        if (str_starts_with($property, 'assignments.')) {
            $this->recalculatePreview();
        }
    }

    protected function recalculatePreview()
    {
        foreach ($this->order->items as $item) {
            $itemId = $item->id;
            $base   = $this->preview[$itemId]['base'];
            $rows   = $this->assignments[$itemId];

            // Validasi total nominal tetap tidak melebihi dasar komisi
            $fixedTotal = collect($rows)
                ->where('split_type', 'fixed_amount')
                ->sum(fn ($row) => (float) ($row['split_value'] ?? 0));

            if ($fixedTotal > $base) {
                $this->addError(
                    "assignments.{$itemId}",
                    'Total nominal tetap (Rp' . number_format($fixedTotal, 0, ',', '.') .
                    ') melebihi Dasar Komisi (Rp' . number_format($base, 0, ',', '.') . ').'
                );
            } else {
                $this->resetErrorBag("assignments.{$itemId}");
            }

            // ...lanjutkan logic perhitungan $this->preview[$itemId]['rows'] yang sudah ada di sini
        }
    }

    public function save(bool $finalize = false): void
    {
        $this->recalculatePreview();

        if ($this->getErrorBag()->any()) {
            $this->dispatch('notify', message: 'Ada kesalahan pada pembagian komisi, silakan periksa kembali.', type: 'error');
            return;
        }
        abort_if($this->order->status === 'completed' || $this->order->status === 'paid', 403, 'Komisi sudah dikunci, tidak bisa diubah.');

        foreach ($this->order->items as $item) {
            $rows = collect($this->assignments[$item->id] ?? [])->filter(fn ($r) => filled($r['employee_id']));

            if ($finalize && $rows->isEmpty()) {
                $this->dispatch('notify', message: "Item '{$item->service_name_snapshot}' belum punya karyawan yang ditugaskan.", type: 'error');
                return;
            }

            $percentageRows = $rows->where('split_type', 'percentage');
            if ($percentageRows->isNotEmpty()) {
                $sum = $percentageRows->sum(fn ($r) => (float) ($r['split_value'] ?? 0));
                if (abs($sum - 100) > 0.01) {
                    $this->dispatch('notify', message: "Total persentase untuk '{$item->service_name_snapshot}' harus 100% (saat ini {$sum}%).", type: 'error');
                    return;
                }
            }
        }

        DB::transaction(function () use ($finalize) {
            $preview = $this->preview;

            foreach ($this->order->items as $item) {
                OrderItemWorker::where('order_item_id', $item->id)->delete();

                foreach ($this->assignments[$item->id] ?? [] as $i => $row) {
                    if (blank($row['employee_id'])) {
                        continue;
                    }

                    OrderItemWorker::create([
                        'order_item_id' => $item->id,
                        'employee_id' => $row['employee_id'],
                        'split_type' => $row['split_type'],
                        'split_value' => $row['split_type'] === 'equal' ? null : $row['split_value'],
                        'commission_amount' => $preview[$item->id]['rows'][$i] ?? 0,
                    ]);
                }
            }

            if ($finalize) {
                $this->order->update(['status' => 'completed']);
            }
        });

        $this->dispatch('notify', message: $finalize ? 'Order diselesaikan, komisi terkunci.' : 'Assignment tersimpan.');

        if ($finalize) {
            $this->redirect(route('app.orders'), navigate: false);
        } else {
            $this->order->refresh();
        }
    }

    public function render()
    {
        return view('livewire.app.orders.workers', [
            'employees' => $this->employees(),
            'preview' => $this->preview,
        ]);
    }
}